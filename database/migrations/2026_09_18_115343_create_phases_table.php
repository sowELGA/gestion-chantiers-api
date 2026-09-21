<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->string('nomPhase');
            $table->enum('typePhase', ['gros_oeuvre', 'second_oeuvre', 'finitions', 'autre']);
            $table->string('sous_traitant')->nullable();
            $table->integer('ordre')->default(1);
            $table->date('date_debut')->nullable();
            $table->date('date_fin_prevue')->nullable();
            $table->enum('statutPhase', ['en_attente', 'en_cours', 'terminee'])->default('en_attente');
            $table->boolean('est_en_retard')->default(false);
            $table->foreignId('chantier_id')->constrained('chantiers', 'id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phases');
    }
};
