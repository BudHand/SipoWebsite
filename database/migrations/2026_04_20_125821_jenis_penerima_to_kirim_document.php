<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kirim_document', function (Blueprint $table) {
            if (!Schema::hasColumn('kirim_document', 'jenis_penerima')) {
                $table->string('jenis_penerima', 50)->nullable()->after('id_penerima');
                $table->index('jenis_penerima', 'kirim_document_jenis_penerima_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kirim_document', function (Blueprint $table) {
            if (Schema::hasColumn('kirim_document', 'jenis_penerima')) {
                $table->dropIndex('kirim_document_jenis_penerima_index');
                $table->dropColumn('jenis_penerima');
            }
        });
    }
};
