<?php

namespace App\Services\Preview;

use Illuminate\Support\Facades\Log;

class ColorExtractionService
{
    /**
     * Extract dominant brand colors from an image path/URL, or fallback to template default
     */
    public function resolvePalette(?string $imagePath, array $defaultPalette, ?array $manualOverrides = null): array
    {
        $palette = $defaultPalette;

        // If an image is provided and exists, perform color quantization
        if (!empty($imagePath) && file_exists($imagePath)) {
            try {
                $extracted = $this->extractFromLocalImage($imagePath);
                if (!empty($extracted['primary'])) {
                    $palette['primary'] = $extracted['primary'];
                    if (!empty($extracted['secondary'])) {
                        $palette['secondary'] = $extracted['secondary'];
                    }
                    if (!empty($extracted['accent'])) {
                        $palette['accent'] = $extracted['accent'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Color extraction failed for image: ' . $e->getMessage());
            }
        }

        // Apply any manual overrides from sales/admin
        if (!empty($manualOverrides)) {
            foreach (['primary', 'secondary', 'accent', 'bg'] as $key) {
                if (!empty($manualOverrides[$key])) {
                    $palette[$key] = $manualOverrides[$key];
                }
            }
        }

        // Ensure all required color keys exist with fallbacks
        return [
            'primary' => $palette['primary'] ?? '#1E3A5F',
            'secondary' => $palette['secondary'] ?? '#0F172A',
            'accent' => $palette['accent'] ?? '#0EA5E9',
            'bg' => $palette['bg'] ?? '#F8FAFC',
        ];
    }

    /**
     * Quantize image and find dominant non-white/non-black colors using PHP GD
     */
    protected function extractFromLocalImage(string $path): array
    {
        if (!extension_loaded('gd')) {
            return [];
        }

        $info = @getimagesize($path);
        if (!$info) {
            return [];
        }

        $mime = $info['mime'] ?? '';
        $src = null;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $src = @imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $src = @imagecreatefrompng($path);
                break;
            case 'image/webp':
                $src = @imagecreatefromwebp($path);
                break;
        }

        if (!$src) {
            return [];
        }

        // Resize down to 60x60 thumbnail for fast color quantization
        $w = imagesx($src);
        $h = imagesy($src);
        $thumb = imagecreatetruecolor(60, 60);
        imagecopyresampled($thumb, $src, 0, 0, 0, 0, 60, 60, $w, $h);

        $colorBuckets = [];
        for ($x = 0; $x < 60; $x += 3) {
            for ($y = 0; $y < 60; $y += 3) {
                $rgb = imagecolorat($thumb, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Filter out extreme whites, dark blacks, and transparent areas
                $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
                if ($brightness < 20 || $brightness > 240) {
                    continue;
                }

                // Quantize to 32-value steps to group similar hues
                $qr = (int)(round($r / 32) * 32);
                $qg = (int)(round($g / 32) * 32);
                $qb = (int)(round($b / 32) * 32);

                $hex = sprintf("#%02x%02x%02x", min(255, $qr), min(255, $qg), min(255, $qb));
                $colorBuckets[$hex] = ($colorBuckets[$hex] ?? 0) + 1;
            }
        }

        imagedestroy($thumb);
        imagedestroy($src);

        arsort($colorBuckets);
        $topHexes = array_keys($colorBuckets);

        if (empty($topHexes)) {
            return [];
        }

        $primary = $topHexes[0];
        $secondary = $topHexes[1] ?? $this->darkenHex($primary, 0.3);
        $accent = $topHexes[2] ?? $this->lightenHex($primary, 0.4);

        return [
            'primary' => strtoupper($primary),
            'secondary' => strtoupper($secondary),
            'accent' => strtoupper($accent),
        ];
    }

    protected function darkenHex(string $hex, float $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = max(0, min(255, (int)(hexdec(substr($hex, 0, 2)) * (1 - $percent))));
        $g = max(0, min(255, (int)(hexdec(substr($hex, 2, 2)) * (1 - $percent))));
        $b = max(0, min(255, (int)(hexdec(substr($hex, 4, 2)) * (1 - $percent))));
        return sprintf("#%02X%02X%02X", $r, $g, $b);
    }

    protected function lightenHex(string $hex, float $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = max(0, min(255, (int)(hexdec(substr($hex, 0, 2)) + (255 - hexdec(substr($hex, 0, 2))) * $percent)));
        $g = max(0, min(255, (int)(hexdec(substr($hex, 2, 2)) + (255 - hexdec(substr($hex, 2, 2))) * $percent)));
        $b = max(0, min(255, (int)(hexdec(substr($hex, 4, 2)) + (255 - hexdec(substr($hex, 4, 2))) * $percent)));
        return sprintf("#%02X%02X%02X", $r, $g, $b);
    }
}
