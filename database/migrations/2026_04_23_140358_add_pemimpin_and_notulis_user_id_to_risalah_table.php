<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risalah', function (Blueprint $table) {
            if (!Schema::hasColumn('risalah', 'pemimpin_acara_user_id')) {
                $table->unsignedBigInteger('pemimpin_acara_user_id')
                    ->nullable()
                    ->after('nama_pemimpin_acara');

                $table->index('pemimpin_acara_user_id', 'risalah_pemimpin_acara_user_id_index');
            }

            if (!Schema::hasColumn('risalah', 'notulis_acara_user_id')) {
                $table->unsignedBigInteger('notulis_acara_user_id')
                    ->nullable()
                    ->after('nama_notulis_acara');

                $table->index('notulis_acara_user_id', 'risalah_notulis_acara_user_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('risalah', function (Blueprint $table) {
            if (Schema::hasColumn('risalah', 'pemimpin_acara_user_id')) {
                $table->dropIndex('risalah_pemimpin_acara_user_id_index');
                $table->dropColumn('pemimpin_acara_user_id');
            }

            if (Schema::hasColumn('risalah', 'notulis_acara_user_id')) {
                $table->dropIndex('risalah_notulis_acara_user_id_index');
                $table->dropColumn('notulis_acara_user_id');
            }
        });
    }
};
