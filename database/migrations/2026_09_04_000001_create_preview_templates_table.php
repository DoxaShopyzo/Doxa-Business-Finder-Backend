<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preview_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name', 100);
            $table->enum('type', ['website', 'app']);
            $table->string('category_key', 60);
            $table->text('description')->nullable();
            $table->json('default_palette'); // {"primary": "#...", "secondary": "#...", "accent": "#...", "bg": "#..."}
            $table->json('sample_content')->nullable(); // Default headings, features, service items
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'category_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preview_templates');
    }
};
