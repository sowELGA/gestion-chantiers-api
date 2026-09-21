<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            $table->string('nomTache');
            $table->date('date_debut_prevue');
            $table->date('date_fin_prevue');
            $table->date('date_debut_reelle')->nullable();
            $table->date('date_fin_reelle')->nullable();
            $table->integer('avancement')->default(0);
            $table->enum('statutTache', ['en_attente', 'en_cours', 'terminee'])->default('en_attente');
            $table->boolean('est_en_retard')->default(false);
            $table->foreignId('chantier_id')->constrained('chantiers', 'id')->onDelete('cascade');
            $table->foreignId('phase_id')->constrained('phases', 'id')->onDelete('cascade');
            $table->foreignId('tache_precedente_id')->nullable()->constrained('taches', 'id')->onDelete('set null');
            $table->foreignId('responsable_id')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};
