<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recaps_hebdomadaires', function (Blueprint $table) {
            $table->id();
            $table->integer('semaine');
            $table->integer('annee');
            $table->enum('statutRecap', ['en_attente', 'soumise', 'validee_cp', 'rejetee', 'envoyee_direction'])->default('en_attente');
            $table->text('motif_rejet')->nullable();
            $table->timestamp('valide_le')->nullable();
            $table->foreignId('ouvrier_id')->constrained('ouvriers', 'id')->onDelete('restrict');
            $table->foreignId('chantier_id')->constrained('chantiers', 'id')->onDelete('restrict');
            $table->foreignId('soumis_par_id')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recaps_hebdomadaires');
    }
};
