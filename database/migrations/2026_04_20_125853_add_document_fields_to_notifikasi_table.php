<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            if (!Schema::hasColumn('notifikasi', 'id_document')) {
                $table->unsignedBigInteger('id_document')->nullable()->after('judul_document');
                $table->index('id_document', 'notifikasi_id_document_index');
            }

            if (!Schema::hasColumn('notifikasi', 'jenis_document')) {
                $table->string('jenis_document', 50)->nullable()->after('id_document');
                $table->index('jenis_document', 'notifikasi_jenis_document_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            if (Schema::hasColumn('notifikasi', 'jenis_document')) {
                $table->dropIndex('notifikasi_jenis_document_index');
                $table->dropColumn('jenis_document');
            }

            if (Schema::hasColumn('notifikasi', 'id_document')) {
                $table->dropIndex('notifikasi_id_document_index');
                $table->dropColumn('id_document');
            }
        });
    }
};
