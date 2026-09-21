<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses_chantiers', function (Blueprint $table) {
            $table->id();
            $table->enum('categorie', ['materiaux', 'materiels', 'salaires', 'autre']);
            $table->decimal('montant', 12, 2);
            $table->string('description');
            $table->date('date_depense');
            $table->foreignId('chantier_id')->constrained('chantiers', 'id')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses_chantiers');
    }
};
