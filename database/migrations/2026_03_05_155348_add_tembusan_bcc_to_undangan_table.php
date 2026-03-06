<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('undangan', function (Blueprint $table) {
            $table->longText('tembusan')->nullable()->after('tujuan');
            $table->longText('bcc')->nullable()->after('tembusan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('undangan', function (Blueprint $table) {
            $table->dropColumn(['tembusan', 'bcc']);
        });
    }
};
