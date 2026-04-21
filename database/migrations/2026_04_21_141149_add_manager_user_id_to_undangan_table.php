<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('undangan', function (Blueprint $table) {
            if (!Schema::hasColumn('undangan', 'manager_user_id')) {
                $table->unsignedBigInteger('manager_user_id')
                      ->nullable()
                      ->after('nama_bertandatangan');

                $table->index('manager_user_id', 'undangan_manager_user_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('undangan', function (Blueprint $table) {
            if (Schema::hasColumn('undangan', 'manager_user_id')) {
                $table->dropIndex('undangan_manager_user_id_index');
                $table->dropColumn('manager_user_id');
            }
        });
    }
};
