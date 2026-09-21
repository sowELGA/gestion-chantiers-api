<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('statutPointage', ['present', 'absent']);
            $table->integer('heures_sup')->default(0);
            $table->decimal('taux_journalier', 10, 2)->nullable();
            $table->decimal('taux_heure_sup', 10, 2)->nullable();
            $table->foreignId('poste_id')->nullable()->constrained('postes', 'id')->onDelete('set null');
            $table->foreignId('ouvrier_id')->constrained('ouvriers', 'id')->onDelete('restrict');
            $table->foreignId('chantier_id')->constrained('chantiers', 'id')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};
