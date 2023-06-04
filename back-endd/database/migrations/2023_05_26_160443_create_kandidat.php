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
        Schema::create('kandidat', function (Blueprint $table) {
            $table->id('IdKandidat');
            $table->unsignedbigInteger('IdUser');
            $table->unsignedbigInteger('IdPemilihan');
            $table->text('visi');
            $table->text('misi');
            $table->string('gambar');
            $table->enum('setuju', ['ya', 'tidak'])->default('tidak');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('IdUser')->references('IdUser')->on('user')->onDelete('cascade');
            $table->foreign('IdPemilihan')->references('IdPemilihan')->on('pemilihan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kandidat');
    }
};
