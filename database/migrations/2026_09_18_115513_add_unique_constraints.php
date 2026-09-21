<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->unique(['ouvrier_id', 'chantier_id', 'date'], 'pointages_ouvrier_chantier_date_unique');
            $table->index(['chantier_id', 'date'], 'pointages_chantier_date_index');
        });

        Schema::table('taux_salaires', function (Blueprint $table) {
            $table->unique(['poste_id', 'chantier_id'], 'taux_salaires_poste_chantier_unique');
        });

        Schema::table('recaps_hebdomadaires', function (Blueprint $table) {
            $table->unique(['ouvrier_id', 'chantier_id', 'semaine', 'annee'], 'recaps_ouvrier_chantier_semaine_annee_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->dropUnique('pointages_ouvrier_chantier_date_unique');
            $table->dropIndex('pointages_chantier_date_index');
        });

        Schema::table('taux_salaires', function (Blueprint $table) {
            $table->dropUnique('taux_salaires_poste_chantier_unique');
        });

        Schema::table('recaps_hebdomadaires', function (Blueprint $table) {
            $table->dropUnique('recaps_ouvrier_chantier_semaine_annee_unique');
        });
    }
};
