<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_risalah_details', function (Blueprint $table) {
            $table->bigIncrements('id_sub_risalah_detail');
            $table->unsignedBigInteger('risalah_detail_id_risalah_detail');
            $table->text('topik')->nullable();
            $table->text('pembahasan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->text('target')->nullable();
            $table->text('pic')->nullable();
            $table->timestamps();

            $table->foreign('risalah_detail_id_risalah_detail')
                ->references('id_risalah_detail')
                ->on('risalah_details')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_risalah_details');
    }
};
