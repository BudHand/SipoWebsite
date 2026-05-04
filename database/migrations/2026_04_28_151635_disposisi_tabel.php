<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposisi', function (Blueprint $table) {
            $table->id();

            // Polymorphic ke Memo atau Undangan
            $table->string('document_type');   // 'memo' atau 'undangan'
            $table->unsignedBigInteger('document_id');
            $table->index(['document_type', 'document_id']);

            // Siapa memberi disposisi
            $table->unsignedBigInteger('dari_user_id');
            $table->foreign('dari_user_id')->references('id')->on('users');

            // Siapa menerima disposisi
            $table->unsignedBigInteger('kepada_user_id');
            $table->foreign('kepada_user_id')->references('id')->on('users');

            // Isi disposisi
            $table->text('instruksi');
            $table->text('catatan')->nullable();
            $table->date('deadline')->nullable();

            // Status: menunggu → diterima → selesai (atau diteruskan)
            $table->enum('status', ['menunggu', 'diterima', 'selesai', 'diteruskan'])
                  ->default('menunggu');

            // Jika disposisi ini hasil meneruskan dari disposisi lain
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('disposisi');

            // Tracking kapan dibaca
            $table->timestamp('dibaca_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisi');
    }
};
