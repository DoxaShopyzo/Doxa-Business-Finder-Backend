<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GooglePlacesService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->apiKey = config('doxa.google_places.api_key', '') ?? '';
        $this->baseUrl = config('doxa.google_places.base_url', 'https://places.googleapis.com/v1/places');
        $this->cacheTtl = config('doxa.google_places.cache_ttl', 86400);
    }

    /**
     * Search businesses — called by SearchController
     * Completely unconstrained deep multi-zone extraction: scans all sub-localities & all pages with zero artificial limits.
     */
    public function search(string $query, string $location, ?string $category = null, int $radius = 5000): array
    {
        if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_GOOGLE_PLACES_API_KEY') {
            $allResults = [];
            $quotaExceeded = false;

            try {
                $isSpecificSubArea = str_contains($location, ',');
                if ($isSpecificSubArea) {
                    // User selected a specific locality (e.g. "Anna Nagar, Chennai", "RS Puram, Coimbatore")
                    // Scan this specific zone thoroughly across all pages
                    $locationsToScan = [$location];
                } else {
                    // User selected an entire district (e.g. "Chennai", "Coimbatore", "Tiruppur")
                    // Scan main district PLUS ALL sub-localities for 100% UNLIMITED lead extraction
                    $subAreas = $this->getSubLocalitiesForDistrict($location);
                    $locationsToScan = array_values(array_unique(array_merge([$location], $subAreas)));
                }

                foreach ($locationsToScan as $loc) {
                    try {
                        // Fetch all available pages for this zone (up to 3 pages / 60 places per zone)
                        $zoneResults = $this->textSearch($query, $loc, $radius, $category, 5000);
                        foreach ($zoneResults as $res) {
                            $allResults[] = $res;
                        }
                    } catch (\Throwable $ze) {
                        if (str_contains($ze->getMessage(), '429') || str_contains($ze->getMessage(), 'RESOURCE_EXHAUSTED')) {
                            $quotaExceeded = true;
                            Log::warning("Google Places API quota limit reached while scanning {$loc}. Returning all collected businesses.");
                            break;
                        }
                    }
                }

                // ── STRICT LOCATION FILTER ──────────────────────────────────────
                // Google's text search is fuzzy and may return businesses from OTHER
                // districts. Filter out any result whose formatted_address does NOT
                // contain the searched district/city name.
                $allResults = $this->filterByLocation($allResults, $location);

                // ── STRICT KEYWORD RELEVANCE FILTER ─────────────────────────────
                // Google returns ALL churches/businesses in an area regardless of
                // denomination. Filter to keep ONLY results matching the searched
                // keyword (e.g., "CNI Church" → only show CNI churches, not CSI/Catholic).
                $allResults = $this->filterByKeyword($allResults, $query);

                // Apply smart multi-tier deduplication (Place ID, normalized phone, clean business name + area)
                $allResults = $this->deduplicateResults($allResults);

                // If live search collected businesses (even if Google cut off midway), return ALL of them!
                if (!empty($allResults)) {
                    Log::info("Unlimited search successfully collected " . count($allResults) . " unique businesses for '{$query} in {$location}'");
                    return $allResults;
                }

                // Database Fallback: If Google API quota is exhausted before returning any businesses,
                // retrieve previously discovered businesses from the database without any limits
                $cityKeyword = trim(explode(',', $location)[0]);
                $cachedDb = \App\Models\SearchResult::where(function ($w) use ($location, $cityKeyword) {
                        $w->where('formatted_address', 'LIKE', "%{$location}%")
                          ->orWhere('formatted_address', 'LIKE', "%{$cityKeyword}%");
                    })
                    ->when($query, function ($q) use ($query) {
                        $q->where(function ($sub) use ($query) {
                            $sub->where('business_name', 'LIKE', "%{$query}%")
                                ->orWhere('category', 'LIKE', "%{$query}%");
                        });
                    })
                    ->latest()
                    ->get();

                if ($cachedDb->isNotEmpty()) {
                    $mapped = $cachedDb->map(function ($sr) {
                        return [
                            'place_id' => $sr->place_reference ?? 'cached_' . $sr->id,
                            'name' => $sr->business_name,
                            'address' => $sr->formatted_address,
                            'phone' => $sr->phone,
                            'website' => $sr->website,
                            'rating' => $sr->rating,
                            'review_count' => $sr->review_count,
                            'category' => $sr->category,
                            'latitude' => $sr->latitude,
                            'longitude' => $sr->longitude,
                            'opportunity_score' => $sr->opportunity_score,
                            'source' => 'google_places_cached',
                        ];
                    })->toArray();

                    $uniqueCached = $this->deduplicateResults($mapped);
                    if (!empty($uniqueCached)) {
                        Log::info("Returned " . count($uniqueCached) . " unique cached DB businesses for '{$query} in {$location}' due to Google API quota limit");
                        return $uniqueCached;
                    }
                }

                if ($quotaExceeded) {
                    throw new \Exception("Google Places daily search quota reached on Google Cloud Console. Please increase your quota limit in Google Cloud Console (APIs & Services -> Places API (New) -> Quotas) to Unlimited or wait for daily reset.");
                }

                Log::warning('Google Places API returned 0 results', ['query' => $query, 'location' => $location]);
                return [];
            } catch (\Throwable $e) {
                Log::error('Google Places API search failed: ' . $e->getMessage(), [
                    'query' => $query,
                    'location' => $location,
                ]);
                throw $e;
            }
        }

        Log::error('Google Places API key not configured. Cannot perform search.');
        return [];
    }

    /**
     * Smart Multi-Tier Deduplication:
     * 1. Exact Place ID (normalizing prefixes)
     * 2. Cleaned Phone Number (normalizing all digits to last 10 digits)
     * 3. Normalized Business Name + Locality (stripping legal suffixes, punctuation, extra spaces)
     */
    public function deduplicateResults(array $results): array
    {
        $unique = [];
        $seenPlaceIds = [];
        $seenPhones = [];
        $seenNameLoc = [];

        foreach ($results as $res) {
            $placeId = $res['place_id'] ?? null;
            $name = trim($res['name'] ?? $res['business_name'] ?? '');
            $phone = trim($res['phone'] ?? '');
            $address = trim($res['address'] ?? $res['formatted_address'] ?? '');

            if (empty($name)) {
                continue;
            }

            // 1. Check Place ID
            if (!empty($placeId)) {
                $cleanPlaceId = str_replace('places/', '', $placeId);
                if (isset($seenPlaceIds[$cleanPlaceId])) {
                    continue;
                }
            }

            // 2. Check Cleaned Phone (compare last 10 digits)
            $cleanPhone = preg_replace('/\D+/', '', $phone);
            if (strlen($cleanPhone) >= 10) {
                $last10 = substr($cleanPhone, -10);
                if (isset($seenPhones[$last10])) {
                    continue;
                }
            }

            // 3. Check Normalized Name + Locality
            $normName = strtolower($name);
            $normName = preg_replace('/\b(pvt|ltd|private|limited|\(p\)|store|shop)\b/i', '', $normName);
            $normName = preg_replace('/[^\w\s]/', '', $normName);
            $normName = preg_replace('/\s+/', ' ', trim($normName));
            if (empty($normName)) {
                $normName = strtolower(trim($name));
            }

            $normAddr = strtolower($address);
            $addrParts = array_filter(explode(',', $normAddr), fn($t) => strlen(trim($t)) > 2);
            $areaToken = !empty($addrParts) ? trim(array_values($addrParts)[0]) : '';
            $areaToken = preg_replace('/[^\w\s]/', '', $areaToken);

            $nameLocKey = $normName . '|' . $areaToken;
            if (isset($seenNameLoc[$nameLocKey]) || isset($seenNameLoc[$normName])) {
                continue;
            }

            // Mark as seen
            if (!empty($cleanPlaceId)) {
                $seenPlaceIds[$cleanPlaceId] = true;
            }
            if (strlen($cleanPhone) >= 10) {
                $seenPhones[$last10] = true;
            }
            $seenNameLoc[$nameLocKey] = true;
            $seenNameLoc[$normName] = true;

            $unique[] = $res;
        }

        return $unique;
    }

    /**
     * Strict Location Filter:
     * Removes results whose formatted_address does NOT match the searched
     * district/city.  Handles both "Chennai" (whole district) and
     * "Anna Nagar, Chennai" (sub-area) search patterns.
     *
     * Matching strategy (case-insensitive, checked against formatted_address):
     *  1. The full $location string (e.g. "Anna Nagar, Chennai")
     *  2. The district/parent keyword extracted from the comma-separated parts
     *     (e.g. "Chennai" from "Anna Nagar, Chennai")
     *  3. Known alternate spellings of the district (e.g. Trichy / Tiruchirappalli)
     *  4. The state name "Tamil Nadu" is NOT sufficient on its own — we need the
     *     specific district keyword in the address.
     */
    protected function filterByLocation(array $results, string $location): array
    {
        if (empty($results) || empty(trim($location))) {
            return $results;
        }

        // Build a list of acceptable location keywords
        $keywords = [];

        // Add the full location
        $keywords[] = strtolower(trim($location));

        // Extract individual parts (e.g. "Anna Nagar" and "Chennai" from "Anna Nagar, Chennai")
        $parts = array_map('trim', explode(',', $location));
        foreach ($parts as $part) {
            if (strlen($part) > 1) {
                $keywords[] = strtolower($part);
            }
        }

        // The primary district is the LAST part (e.g. "Chennai" from "Anna Nagar, Chennai")
        // or the only part if no comma
        $primaryDistrict = strtolower(trim(end($parts)));

        // Add known alternate names / spellings for districts that have variants
        $alternates = [
            'trichy'           => ['tiruchirappalli', 'tiruchi', 'trichy'],
            'tiruchirappalli'  => ['trichy', 'tiruchi'],
            'tiruppur'         => ['tirupur', 'tiruppur'],
            'tirupur'          => ['tiruppur'],
            'thoothukudi'      => ['tuticorin', 'thoothukudi'],
            'tuticorin'        => ['thoothukudi'],
            'krishnagiri'      => ['hosur', 'krishnagiri'],
            'hosur'            => ['krishnagiri', 'hosur'],
            'kanyakumari'      => ['nagercoil', 'kanyakumari', 'cape comorin'],
            'nagercoil'        => ['kanyakumari'],
            'nilgiris'         => ['ooty', 'nilgiris', 'udhagamandalam'],
            'ooty'             => ['nilgiris', 'udhagamandalam'],
            'viluppuram'       => ['villupuram'],
            'villupuram'       => ['viluppuram'],
            'sivakasi'         => ['virudhunagar', 'sivakasi'],
            'virudhunagar'     => ['sivakasi'],
        ];

        if (isset($alternates[$primaryDistrict])) {
            foreach ($alternates[$primaryDistrict] as $alt) {
                $keywords[] = $alt;
            }
        }

        // Deduplicate keywords
        $keywords = array_unique($keywords);

        // Filter: keep only results whose address contains at least one keyword
        $filtered = [];
        $removedCount = 0;

        foreach ($results as $res) {
            $address = strtolower(trim($res['address'] ?? $res['formatted_address'] ?? ''));

            if (empty($address)) {
                // No address to validate — keep the result (benefit of the doubt)
                $filtered[] = $res;
                continue;
            }

            $matched = false;
            foreach ($keywords as $kw) {
                if (str_contains($address, $kw)) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                $filtered[] = $res;
            } else {
                $removedCount++;
            }
        }

        if ($removedCount > 0) {
            Log::info("Location filter removed {$removedCount} out-of-district results for '{$location}'. Keywords checked: " . implode(', ', $keywords));
        }

        return $filtered;
    }

    /**
     * Strict Keyword Relevance Filter:
     * Google's text search for "CNI Church in Ramanathapuram" returns ALL churches
     * (CSI, Catholic, Baptist, etc.) — not just CNI churches. This filter extracts
     * the specific differentiating keyword (e.g., "CNI", "Pentecostal", "Baptist")
     * by stripping generic place-type words, then keeps ONLY results whose business
     * name contains that keyword (or known synonyms).
     */
    protected function filterByKeyword(array $results, string $query): array
    {
        if (empty($results) || empty(trim($query))) {
            return $results;
        }

        // Generic place-type words that don't help differentiate businesses
        $genericWords = [
            'church', 'churches', 'temple', 'temples', 'mosque', 'mosques',
            'masjid', 'kovil', 'centre', 'center', 'mahal', 'hall',
            'shop', 'store', 'stores', 'mart', 'market', 'plaza',
            'business', 'businesses', 'service', 'services', 'agency',
            'the', 'a', 'an', 'and', 'of', 'in', 'near', 'at', 'for',
            'all', 'new', 'old', 'sri', 'shri',
        ];

        // Extract SPECIFIC keywords by removing generic words
        $words = preg_split('/\s+/', strtolower(trim($query)));
        $specificWords = array_values(array_filter($words, function ($w) use ($genericWords) {
            return strlen($w) > 1 && !in_array($w, $genericWords);
        }));

        // No specific keyword found → skip filtering (e.g., query was just "Church" or "All")
        if (empty($specificWords)) {
            return $results;
        }

        // Expand with synonyms so we catch alternate spellings / full names
        $synonymMap = [
            'catholic'    => ['catholic', 'roman catholic', 'r.c.', 'rc '],
            'csi'         => ['csi', 'church of south india', 'c.s.i'],
            'cni'         => ['cni', 'church of north india', 'c.n.i'],
            'ipc'         => ['ipc', 'india pentecostal', 'indian pentecostal', 'i.p.c'],
            'pentecostal' => ['pentecostal', 'pentecost', 'assembly of god', 'apostolic'],
            'baptist'     => ['baptist'],
            'christian'   => ['christian'],
            'prayer'      => ['prayer', 'prarthana'],
            'fellowship'  => ['fellowship'],
            'methodist'   => ['methodist'],
            'lutheran'    => ['lutheran'],
            'adventist'   => ['adventist', 'seventh day'],
            'salvation'   => ['salvation army'],
            'orthodox'    => ['orthodox'],
        ];

        $matchKeywords = [];
        foreach ($specificWords as $sw) {
            $matchKeywords[] = $sw;
            if (isset($synonymMap[$sw])) {
                foreach ($synonymMap[$sw] as $syn) {
                    $matchKeywords[] = $syn;
                }
            }
        }
        $matchKeywords = array_values(array_unique($matchKeywords));

        // Filter: keep only results whose NAME contains at least one specific keyword
        $filtered = [];
        $removedCount = 0;
        $removedNames = [];

        foreach ($results as $res) {
            $name = strtolower(trim($res['name'] ?? $res['business_name'] ?? ''));

            if (empty($name)) {
                $filtered[] = $res;
                continue;
            }

            $matched = false;
            foreach ($matchKeywords as $kw) {
                if (str_contains($name, $kw)) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                $filtered[] = $res;
            } else {
                $removedCount++;
                $removedNames[] = $res['name'] ?? $res['business_name'] ?? '?';
            }
        }

        if ($removedCount > 0) {
            Log::info("Keyword filter removed {$removedCount} non-matching results for '{$query}'. Required keywords: [" . implode(', ', $matchKeywords) . "]. Removed: " . implode(' | ', array_slice($removedNames, 0, 10)));
        }

        return $filtered;
    }

    /**
     * Tier 1: Discovery Search (Text Search Pro SKU - 35,000 free/month)
     * Automatically paginates through all available pages (nextPageToken) to fetch entire business dataset without limit.
     */
    public function textSearch(string $query, string $location, ?int $radius, ?string $category, int $limit = 5000): array
    {
        $cacheKey = 'places_search_pro_all_' . md5(json_encode(func_get_args()));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($query, $location, $category, $limit) {
            $cleanCategory = ($category === 'All' || empty($category)) ? '' : trim($category);
            $cleanQuery = trim($query);

            if (!empty($cleanQuery) && !empty($cleanCategory)) {
                if (stripos($cleanQuery, $cleanCategory) !== false) {
                    $searchTerms = $cleanQuery;
                } elseif (stripos($cleanCategory, $cleanQuery) !== false) {
                    $searchTerms = $cleanCategory;
                } else {
                    $searchTerms = $cleanQuery . ' ' . $cleanCategory;
                }
            } elseif (!empty($cleanQuery)) {
                $searchTerms = $cleanQuery;
            } elseif (!empty($cleanCategory)) {
                $searchTerms = $cleanCategory;
            } else {
                $searchTerms = 'Businesses';
            }

            $textQuery = trim($searchTerms . ' in ' . $location);
            $allPlaces = [];
            $pageToken = null;
            $maxPages = 3; // Google Places Text Search allows maximum 3 pages (60 places per text query)
            $pageCount = 0;

            do {
                $payload = [
                    'textQuery' => $textQuery,
                    'pageSize' => 20,
                ];
                if ($pageToken) {
                    $payload['pageToken'] = $pageToken;
                }

                $response = Http::withoutVerifying()->timeout(20)->withHeaders([
                    'X-Goog-Api-Key' => $this->apiKey,
                    'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.internationalPhoneNumber,places.websiteUri,places.rating,places.userRatingCount,places.primaryType,places.location,places.businessStatus,places.googleMapsUri,nextPageToken'
                ])->post("{$this->baseUrl}:searchText", $payload);

                if ($response->failed()) {
                    Log::error('Google Places API (Text Search Pro) Error', ['body' => $response->body(), 'status' => $response->status()]);
                    if ($pageCount === 0) {
                        throw new \Exception('Google Places API Error: ' . $response->body());
                    }
                    break;
                }

                $data = $response->json();
                $places = $data['places'] ?? [];
                $allPlaces = array_merge($allPlaces, $places);
                $pageToken = $data['nextPageToken'] ?? null;
                $pageCount++;

                if ($pageToken && $pageCount < $maxPages) {
                    usleep(300000); // 300ms delay for next page token activation
                }
            } while ($pageToken && $pageCount < $maxPages && count($allPlaces) < $limit);

            return $this->normalizeResults($allPlaces);
        });
    }

    /**
     * Tier 2: Qualified Lead Enterprise Detail Fetch (Place Details Enterprise SKU - 7,000 free/month)
     * Fetches Contact Data (Phone, Website) and Atmosphere Data (Rating, User Rating Count).
     */
    public function getPlaceDetails(string $placeId): array
    {
        $cacheKey = 'place_details_ent_' . $placeId;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($placeId) {
            if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_GOOGLE_PLACES_API_KEY') {
                try {
                    $response = Http::withoutVerifying()->withHeaders([
                        'X-Goog-Api-Key' => $this->apiKey,
                        'X-Goog-FieldMask' => 'id,displayName,formattedAddress,nationalPhoneNumber,internationalPhoneNumber,websiteUri,rating,userRatingCount,primaryType,location,businessStatus,googleMapsUri'
                    ])->timeout(10)->get("{$this->baseUrl}/{$placeId}");

                    if ($response->successful()) {
                        $place = $response->json() ?? [];
                        return $this->normalizeSingleResult($place);
                    }
                } catch (\Throwable $e) {
                    Log::warning("Place details fetch for {$placeId} handled: " . $e->getMessage());
                }
            }

            Log::warning("Place details unavailable for {$placeId} - API key missing or fetch failed");
            return [
                'place_id' => $placeId,
                'name' => null,
                'address' => null,
                'phone' => null,
                'website' => null,
                'rating' => null,
                'review_count' => 0,
                'category' => null,
                'latitude' => null,
                'longitude' => null,
                'business_status' => 'UNKNOWN',
                'maps_url' => null,
            ];
        });
    }

    protected function normalizeResults(array $places): array
    {
        return array_map(fn($place) => $this->normalizeSingleResult($place), $places);
    }

    protected function normalizeSingleResult(array $place): array
    {
        $displayName = $place['displayName']['text'] ?? ($place['displayName'] ?? ($place['name'] ?? 'Local Business'));
        
        return [
            'place_id' => $place['id'] ?? $place['place_id'] ?? null,
            'name' => $displayName,
            'business_name' => $displayName,
            'address' => $place['formattedAddress'] ?? $place['address'] ?? null,
            'phone' => $place['nationalPhoneNumber'] ?? $place['internationalPhoneNumber'] ?? $place['phone'] ?? null,
            'website' => !empty($place['websiteUri'] ?? $place['website'] ?? null)
                ? Str::limit($place['websiteUri'] ?? $place['website'], 2048, '')
                : null,
            'rating' => isset($place['rating']) ? (float)$place['rating'] : null,
            'review_count' => (int)($place['userRatingCount'] ?? $place['review_count'] ?? 0),
            'category' => $place['primaryType'] ?? $place['category'] ?? 'Business',
            'latitude' => isset($place['location']['latitude']) ? (float)$place['location']['latitude'] : 13.0827,
            'longitude' => isset($place['location']['longitude']) ? (float)$place['location']['longitude'] : 80.2707,
            'business_status' => $place['businessStatus'] ?? 'OPERATIONAL',
            'maps_url' => $place['googleMapsUri'] ?? null,
        ];
    }


    protected function getSubLocalitiesForDistrict(string $district): array
    {
        $d = strtolower($district);

        if (str_contains($d, 'chennai')) {
            return ['Anna Nagar, Chennai', 'T. Nagar, Chennai', 'Guindy, Chennai', 'Adyar, Chennai', 'Velachery, Chennai', 'Ambattur, Chennai', 'Porur, Chennai', 'Perungudi, Chennai', 'Tambaram, Chennai', 'Sriperumbudur, Chennai', 'Thirumudivakkam, Chennai', 'Ekkattuthangal, Chennai', 'Vandalur, Chennai'];
        }
        if (str_contains($d, 'coimbatore')) {
            return ['Gandhipuram, Coimbatore', 'RS Puram, Coimbatore', 'Peelamedu, Coimbatore', 'Saibaba Colony, Coimbatore', 'Saravanampatti, Coimbatore', 'Singanallur, Coimbatore', 'Ganapathy, Coimbatore', 'Kurichi, Coimbatore', 'Sidco, Coimbatore', 'Thudiyalur, Coimbatore'];
        }
        if (str_contains($d, 'tiruppur') || str_contains($d, 'tirupur')) {
            return ['Veerapandi, Tiruppur', 'Palladam, Tiruppur', 'Kangeyam Road, Tiruppur', 'Dharapuram Road, Tiruppur', 'Avinashi Road, Tiruppur', 'Uthukuli, Tiruppur', 'Anupparpalayam, Tiruppur'];
        }
        if (str_contains($d, 'madurai')) {
            return ['Kappalur, Madurai', 'Simmakkal, Madurai', 'Mattuthavani, Madurai', 'Anna Nagar, Madurai', 'Thirumangalam, Madurai', 'Melur, Madurai'];
        }
        if (str_contains($d, 'salem')) {
            return ['Steel Plant Road, Salem', 'Attur, Salem', 'Suramangalam, Salem', 'Fairlands, Salem', 'Mettur, Salem', 'Sankari, Salem'];
        }
        if (str_contains($d, 'erode')) {
            return ['Perundurai, Erode', 'Bhavani, Erode', 'Solar, Erode', 'Chithode, Erode', 'Gobichettipalayam, Erode', 'Sathyamangalam, Erode'];
        }
        if (str_contains($d, 'trichy') || str_contains($d, 'tiruchirappalli')) {
            return ['Srirangam, Trichy', 'Thiruverumbur, Trichy', 'Thillai Nagar, Trichy', 'Manapparai, Trichy', 'Manachanallur, Trichy'];
        }
        if (str_contains($d, 'hosur') || str_contains($d, 'krishnagiri')) {
            return ['SIPCOT Phase 1, Hosur', 'SIPCOT Phase 2, Hosur', 'Bagalur Road, Hosur', 'Bargur, Krishnagiri', 'Shoolagiri, Krishnagiri'];
        }
        if (str_contains($d, 'tirunelveli')) {
            return ['Palayamkottai, Tirunelveli', 'Gangaikondan, Tirunelveli', 'Ambasamudram, Tirunelveli', 'Nanguneri, Tirunelveli'];
        }
        if (str_contains($d, 'thoothukudi') || str_contains($d, 'tuticorin')) {
            return ['SIPCOT, Thoothukudi', 'Kovilpatti, Thoothukudi', 'Tiruchendur, Thoothukudi', 'Arumuganeri, Thoothukudi'];
        }
        if (str_contains($d, 'vellore')) {
            return ['Katpadi, Vellore', 'Gudiyatham, Vellore', 'Anaicut, Vellore', 'Pallikonda, Vellore'];
        }
        if (str_contains($d, 'thanjavur')) {
            return ['Kumbakonam, Thanjavur', 'Pattukkottai, Thanjavur', 'Thiruvaiyaru, Thanjavur', 'Papanasam, Thanjavur'];
        }
        if (str_contains($d, 'virudhunagar') || str_contains($d, 'sivakasi')) {
            return ['Sivakasi, Virudhunagar', 'Rajapalayam, Virudhunagar', 'Aruppukkottai, Virudhunagar', 'Sattur, Virudhunagar'];
        }
        if (str_contains($d, 'chengalpattu')) {
            return ['Tambaram, Chengalpattu', 'Maraimalai Nagar, Chengalpattu', 'Mahindra World City, Chengalpattu', 'Guduvanchery, Chengalpattu', 'Kelambakkam, Chengalpattu'];
        }
        if (str_contains($d, 'kanchipuram')) {
            return ['Sriperumbudur, Kanchipuram', 'Oragadam, Kanchipuram', 'Walajabad, Kanchipuram', 'Kundrathur, Kanchipuram'];
        }
        if (str_contains($d, 'tiruvallur')) {
            return ['Avadi, Tiruvallur', 'Poonamallee, Tiruvallur', 'Gummidipoondi, Tiruvallur', 'Ponneri, Tiruvallur'];
        }
        if (str_contains($d, 'dindigul')) {
            return ['Palani, Dindigul', 'Oddanchatram, Dindigul', 'Kodaikanal, Dindigul', 'Batlagundu, Dindigul'];
        }
        if (str_contains($d, 'karur')) {
            return ['Thanthonimalai, Karur', 'Vengamedu, Karur', 'Pugalur, Karur', 'Kulithalai, Karur'];
        }
        if (str_contains($d, 'namakkal')) {
            return ['Rasipuram, Namakkal', 'Tiruchengode, Namakkal', 'Kumarapalayam, Namakkal', 'Paramathi Velur, Namakkal'];
        }
        if (str_contains($d, 'cuddalore')) {
            return ['Panruti, Cuddalore', 'Chidambaram, Cuddalore', 'Neyveli, Cuddalore', 'Virudhachalam, Cuddalore'];
        }
        if (str_contains($d, 'kanyakumari') || str_contains($d, 'nagercoil')) {
            return ['Nagercoil, Kanyakumari', 'Marthandam, Kanyakumari', 'Colachel, Kanyakumari', 'Kuzhithurai, Kanyakumari'];
        }
        if (str_contains($d, 'theni')) {
            return ['Bodinayakanur, Theni', 'Periyakulam, Theni', 'Cumbum, Theni', 'Chinnamanur, Theni'];
        }
        if (str_contains($d, 'tenkasi')) {
            return ['Courtallam, Tenkasi', 'Sankarankovil, Tenkasi', 'Kadayanallur, Tenkasi', 'Shenkottai, Tenkasi'];
        }
        if (str_contains($d, 'pudukkottai')) {
            return ['Viralimalai, Pudukkottai', 'Aranthangi, Pudukkottai', 'Thirumayam, Pudukkottai', 'Keeranur, Pudukkottai'];
        }
        if (str_contains($d, 'ramanathapuram')) {
            return ['Rameswaram, Ramanathapuram', 'Paramakudi, Ramanathapuram', 'Kilakarai, Ramanathapuram'];
        }
        if (str_contains($d, 'ranipet')) {
            return ['Arcot, Ranipet', 'Walajah, Ranipet', 'Arakkonam, Ranipet', 'Sholinghur, Ranipet'];
        }
        if (str_contains($d, 'sivaganga')) {
            return ['Karaikudi, Sivaganga', 'Devakottai, Sivaganga', 'Manamadurai, Sivaganga', 'Singampunari, Sivaganga'];
        }
        if (str_contains($d, 'tirupathur')) {
            return ['Ambur, Tirupathur', 'Vaniyambadi, Tirupathur', 'Natrampalli, Tirupathur', 'Yelagiri Hills, Tirupathur'];
        }
        if (str_contains($d, 'tiruvannamalai')) {
            return ['Arani, Tiruvannamalai', 'Cheyyar, Tiruvannamalai', 'Polur, Tiruvannamalai', 'Chengam, Tiruvannamalai'];
        }
        if (str_contains($d, 'mayiladuthurai')) {
            return ['Sirkazhi, Mayiladuthurai', 'Tharangambadi, Mayiladuthurai', 'Kuthalam, Mayiladuthurai'];
        }
        if (str_contains($d, 'nagapattinam')) {
            return ['Velankanni, Nagapattinam', 'Vedaranyam, Nagapattinam', 'Kilvelur, Nagapattinam'];
        }
        if (str_contains($d, 'tiruvarur')) {
            return ['Mannargudi, Tiruvarur', 'Thiruthuraipoondi, Tiruvarur', 'Koothanallur, Tiruvarur'];
        }
        if (str_contains($d, 'dharmapuri')) {
            return ['Harur, Dharmapuri', 'Palacode, Dharmapuri', 'Pennagaram, Dharmapuri', 'Karimangalam, Dharmapuri'];
        }
        if (str_contains($d, 'nilgiris') || str_contains($d, 'ooty')) {
            return ['Ooty, Nilgiris', 'Coonoor, Nilgiris', 'Kotagiri, Nilgiris', 'Gudalur, Nilgiris'];
        }
        if (str_contains($d, 'kallakurichi')) {
            return ['Chinnasalem, Kallakurichi', 'Ulundurpet, Kallakurichi', 'Tirukoilur, Kallakurichi'];
        }
        if (str_contains($d, 'viluppuram') || str_contains($d, 'villupuram')) {
            return ['Tindivanam, Viluppuram', 'Gingee, Viluppuram', 'Vanur, Viluppuram', 'Marakkanam, Viluppuram'];
        }
        if (str_contains($d, 'perambalur')) {
            return ['Padalur, Perambalur', 'Kunnam, Perambalur', 'Veppanthattai, Perambalur'];
        }
        if (str_contains($d, 'ariyalur')) {
            return ['Jayankondam, Ariyalur', 'Sendurai, Ariyalur', 'Andimadam, Ariyalur'];
        }

        return [];
    }
}
