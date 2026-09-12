<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaultWeights = [
            'category_relevance' => 25,
            'business_size' => 15,
            'requirement_signals' => 20,
            'footfall_premises' => 15,
            'multiple_branch' => 10,
            'business_activity' => 10,
            'contact_availability' => 5,
        ];

        DB::table('settings')->updateOrInsert(
            [
                'tenant_id' => null,
                'group' => 'ai_analyzer',
                'key' => 'scoring_weights',
            ],
            [
                'value' => json_encode($defaultWeights),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'ai_analyzer')
            ->where('key', 'scoring_weights')
            ->delete();
    }
};
