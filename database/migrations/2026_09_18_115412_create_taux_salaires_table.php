<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taux_salaires', function (Blueprint $table) {
            $table->id();
            $table->decimal('taux_journalier', 10, 2);
            $table->decimal('taux_heure_sup', 10, 2);
            $table->foreignId('poste_id')->constrained('postes', 'id')->onDelete('restrict');
            $table->foreignId('chantier_id')->constrained('chantiers', 'id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taux_salaires');
    }
};
