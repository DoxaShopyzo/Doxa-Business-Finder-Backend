<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preview_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('preview_templates', 'version')) {
                $table->string('version', 10)->default('1.0')->after('category_key');
            }
            if (!Schema::hasColumn('preview_templates', 'preview_image')) {
                $table->string('preview_image')->nullable()->after('version');
            }
            if (!Schema::hasColumn('preview_templates', 'template_config')) {
                $table->json('template_config')->nullable()->after('preview_image');
            }
        });

        Schema::table('generated_previews', function (Blueprint $table) {
            if (!Schema::hasColumn('generated_previews', 'preview_code')) {
                $table->string('preview_code', 32)->nullable()->index()->after('token');
            }
            if (!Schema::hasColumn('generated_previews', 'status')) {
                $table->enum('status', ['draft', 'generated', 'edited', 'ready_to_share', 'shared'])->default('generated')->after('preview_code');
            }
            if (!Schema::hasColumn('generated_previews', 'version')) {
                $table->string('version', 10)->default('1.0')->after('status');
            }
            if (!Schema::hasColumn('generated_previews', 'share_count')) {
                $table->unsignedInteger('share_count')->default(0)->after('version');
            }
            if (!Schema::hasColumn('generated_previews', 'asset_config')) {
                $table->json('asset_config')->nullable()->after('custom_content');
            }
        });

        // Backfill preview_code for existing records
        $previews = DB::table('generated_previews')->select('id')->get();
        foreach ($previews as $p) {
            DB::table('generated_previews')->where('id', $p->id)->update([
                'preview_code' => sprintf('PREVIEW-%06d', $p->id),
                'status' => 'generated',
                'version' => '1.0',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('preview_templates', function (Blueprint $table) {
            $table->dropColumn(['version', 'preview_image', 'template_config']);
        });

        Schema::table('generated_previews', function (Blueprint $table) {
            $table->dropColumn(['preview_code', 'status', 'version', 'share_count', 'asset_config']);
        });
    }
};
