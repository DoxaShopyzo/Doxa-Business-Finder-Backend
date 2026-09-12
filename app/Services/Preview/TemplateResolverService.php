<?php

namespace App\Services\Preview;

use App\Models\PreviewTemplate;
use App\Models\TemplateCategoryMapping;

class TemplateResolverService
{
    /**
     * Resolve the optimal website or app template for a given business category
     */
    public function resolveTemplate(?string $category, string $type = 'website'): PreviewTemplate
    {
        $category = strtolower(trim($category ?? ''));

        if (!empty($category)) {
            // Check category mappings in descending priority
            $mappings = TemplateCategoryMapping::with(['websiteTemplate', 'appTemplate'])
                ->orderBy('priority', 'desc')
                ->get();

            foreach ($mappings as $map) {
                $pattern = strtolower($map->category_pattern);
                $regex = '/' . str_replace('%', '.*', preg_quote($pattern, '/')) . '/i';

                if (preg_match($regex, $category)) {
                    $template = ($type === 'app') ? $map->appTemplate : $map->websiteTemplate;
                    if ($template && $template->is_active) {
                        return $template;
                    }
                }
            }
        }

        // Default Fallback Template
        $fallbackSlug = ($type === 'app') ? 'general_business_app' : 'general_business_site';
        return PreviewTemplate::where('slug', $fallbackSlug)->firstOrFail();
    }
}
