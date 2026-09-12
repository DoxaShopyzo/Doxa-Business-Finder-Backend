<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreviewTemplate extends Model
{
    protected $table = 'preview_templates';

    protected $fillable = [
        'slug',
        'name',
        'type', // website, app
        'category_key',
        'version',
        'preview_image',
        'template_config',
        'description',
        'default_palette',
        'sample_content',
        'is_active',
    ];

    protected $casts = [
        'default_palette' => 'array',
        'sample_content' => 'array',
        'template_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function previews(): HasMany
    {
        return $this->hasMany(GeneratedPreview::class, 'template_id');
    }

    public function websiteMappings(): HasMany
    {
        return $this->hasMany(TemplateCategoryMapping::class, 'website_template_id');
    }

    public function appMappings(): HasMany
    {
        return $this->hasMany(TemplateCategoryMapping::class, 'app_template_id');
    }
}
