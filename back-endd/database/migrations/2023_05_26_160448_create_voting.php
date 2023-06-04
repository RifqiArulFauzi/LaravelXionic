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
        Schema::create('voting', function (Blueprint $table) {
            $table->id('IdVoting');
            $table->unsignedbigInteger('IdUser');
            $table->unsignedbigInteger('IdKandidat'); 
            $table->unsignedbigInteger('IdPemilihan'); 
            $table->timestamp('WaktuVote');
            $table->foreign('IdUser')->references('IdUser')->on('user')->onDelete('cascade');
            $table->foreign('IdKandidat')->references('IdKandidat')->on('kandidat')->onDelete('cascade');
            $table->foreign('IdPemilihan')->references('IdPemilihan')->on('pemilihan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting');
    }
};
