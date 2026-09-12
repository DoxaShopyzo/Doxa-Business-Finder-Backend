<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('category_pattern', 100); // e.g. 'jewel%', 'hospital', 'clinic', 'restaurant'
            $table->unsignedBigInteger('website_template_id');
            $table->unsignedBigInteger('app_template_id');
            $table->integer('priority')->default(10); // higher = evaluated first
            $table->timestamps();

            $table->foreign('website_template_id')->references('id')->on('preview_templates')->onDelete('cascade');
            $table->foreign('app_template_id')->references('id')->on('preview_templates')->onDelete('cascade');
            $table->index('category_pattern');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_category_mappings');
    }
};
