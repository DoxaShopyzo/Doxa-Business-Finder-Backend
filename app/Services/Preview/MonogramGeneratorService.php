<?php

namespace App\Services\Preview;

class MonogramGeneratorService
{
    /**
     * Generate an inline SVG Data-URI monogram for a business
     */
    public function generateMonogramDataUri(string $businessName, string $primaryColor = '#1E3A5F', string $accentColor = '#0EA5E9'): string
    {
        $svg = $this->generateSvg($businessName, $primaryColor, $accentColor);
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    /**
     * Generate raw SVG string
     */
    public function generateSvg(string $businessName, string $primaryColor = '#1E3A5F', string $accentColor = '#0EA5E9'): string
    {
        $initials = $this->extractInitials($businessName);

        // Compute contrasting text color
        $textColor = '#FFFFFF';

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160" width="160" height="160">
  <defs>
    <linearGradient id="brandGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$primaryColor}" />
      <stop offset="100%" stop-color="{$accentColor}" />
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#000000" flood-opacity="0.15" />
    </filter>
  </defs>
  <rect width="160" height="160" rx="36" fill="url(#brandGrad)" filter="url(#shadow)" />
  <text x="50%" y="54%" font-family="system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif" font-size="58" font-weight="800" fill="{$textColor}" text-anchor="middle" dominant-baseline="middle" letter-spacing="1">{$initials}</text>
</svg>
SVG;
    }

    /**
     * Extract 1 or 2 uppercase letters as initials
     */
    public function extractInitials(string $name): string
    {
        // Remove common honorifics or punctuation
        $cleaned = trim(preg_replace('/^(Dr\.|Mr\.|Mrs\.|Shri|Sri|The|M\/s)\s+/i', '', $name));
        $words = preg_split('/[\s\-_,]+/', $cleaned);

        if (empty($words) || empty($words[0])) {
            return 'DB'; // Doxa Business fallback
        }

        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 2));
        }

        $first = substr($words[0], 0, 1);
        $second = substr($words[1], 0, 1);
        return strtoupper($first . $second);
    }
}
