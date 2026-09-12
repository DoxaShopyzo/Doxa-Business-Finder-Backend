<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateCategoryMapping extends Model
{
    protected $table = 'template_category_mappings';

    protected $fillable = [
        'category_pattern',
        'website_template_id',
        'app_template_id',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function websiteTemplate(): BelongsTo
    {
        return $this->belongsTo(PreviewTemplate::class, 'website_template_id');
    }

    public function appTemplate(): BelongsTo
    {
        return $this->belongsTo(PreviewTemplate::class, 'app_template_id');
    }
}
