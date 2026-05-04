<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Drop semua foreign key di kolom kepada_user_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'disposisi'
              AND COLUMN_NAME = 'kepada_user_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE disposisi DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Drop semua index di kolom kepada_user_id
        $indexes = DB::select("
            SELECT DISTINCT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'disposisi'
              AND COLUMN_NAME = 'kepada_user_id'
              AND INDEX_NAME <> 'PRIMARY'
        ");

        foreach ($indexes as $index) {
            DB::statement("ALTER TABLE disposisi DROP INDEX `{$index->INDEX_NAME}`");
        }

        // Buat kolom sementara JSON
        Schema::table('disposisi', function (Blueprint $table) {
            $table->json('kepada_user_id_json')->nullable()->after('kepada_user_id');
        });

        // Pindahkan data lama BIGINT menjadi JSON array
        DB::statement("
            UPDATE disposisi
            SET kepada_user_id_json = JSON_ARRAY(kepada_user_id)
            WHERE kepada_user_id IS NOT NULL
        ");

        // Hapus kolom lama
        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropColumn('kepada_user_id');
        });

        // Rename kolom temporary jadi nama asli
        Schema::table('disposisi', function (Blueprint $table) {
            $table->renameColumn('kepada_user_id_json', 'kepada_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('disposisi', function (Blueprint $table) {
            $table->unsignedBigInteger('kepada_user_id_old')->nullable()->after('kepada_user_id');
        });

        DB::statement("
            UPDATE disposisi
            SET kepada_user_id_old = CAST(JSON_UNQUOTE(JSON_EXTRACT(kepada_user_id, '$[0]')) AS UNSIGNED)
            WHERE JSON_VALID(kepada_user_id)
        ");

        Schema::table('disposisi', function (Blueprint $table) {
            $table->dropColumn('kepada_user_id');
        });

        Schema::table('disposisi', function (Blueprint $table) {
            $table->renameColumn('kepada_user_id_old', 'kepada_user_id');
        });

        DB::statement("
            ALTER TABLE disposisi
            ADD INDEX disposisi_kepada_user_id_index (kepada_user_id)
        ");

        DB::statement("
            ALTER TABLE disposisi
            ADD CONSTRAINT disposisi_kepada_user_id_foreign
            FOREIGN KEY (kepada_user_id) REFERENCES users(id)
        ");
    }
};
