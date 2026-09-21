<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvisionnements', function (Blueprint $table) {
            $table->id();
            $table->string('designation');
            $table->decimal('quantite_demandee', 10, 2);
            $table->string('unite');
            $table->enum('priorite', ['normal', 'urgent'])->default('normal');
            $table->enum('statutAppro', ['en_attente', 'validee', 'rejetee', 'en_cours_livraison', 'partiellement_recue', 'cloturee'])->default('en_attente');
            $table->date('date_livraison_souhaitee')->nullable();
            $table->date('date_commande')->nullable();
            $table->date('date_livraison_prevue')->nullable();
            $table->foreignId('chantier_id')->constrained('chantiers', 'id')->onDelete('restrict');
            $table->foreignId('demandeur_id')->constrained('users', 'id')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvisionnements');
    }
};
