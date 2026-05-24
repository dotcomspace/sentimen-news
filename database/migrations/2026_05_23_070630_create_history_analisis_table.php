<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_analisis', function (Blueprint $table) {
            $table->id();
            $table->string('judul_berita')->nullable();
            $table->text('konten');
            $table->string('hasil_sentimen');
            $table->float('confidence_score')->default(0);
            $table->timestamps(); // created_at & updated_at otomatis
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_analisis');
    }
};