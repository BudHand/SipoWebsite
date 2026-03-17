<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risalah_details', function (Blueprint $table) {
            $table->text('project_event')->nullable()->after('nomor');
        });

        Schema::table('risalah_details', function (Blueprint $table) {
            $table->renameColumn('pembahasan', 'uraian_permasalahan');
            $table->renameColumn('tindak_lanjut', 'pembahasan_tindak_lanjut');
        });
    }

    public function down(): void
    {
        Schema::table('risalah_details', function (Blueprint $table) {
            $table->renameColumn('uraian_permasalahan', 'pembahasan');
            $table->renameColumn('pembahasan_tindak_lanjut', 'tindak_lanjut');
        });

        Schema::table('risalah_details', function (Blueprint $table) {
            $table->dropColumn('project_event');
        });
    }
};
