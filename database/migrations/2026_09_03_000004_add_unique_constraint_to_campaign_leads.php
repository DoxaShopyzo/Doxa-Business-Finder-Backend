<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_leads', function (Blueprint $table) {
            $table->dropIndex('idx_camp_place');
            $table->unique(['campaign_id', 'place_reference'], 'unique_campaign_place');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_leads', function (Blueprint $table) {
            $table->dropUnique('unique_campaign_place');
            $table->index(['campaign_id', 'place_reference'], 'idx_camp_place');
        });
    }
};
