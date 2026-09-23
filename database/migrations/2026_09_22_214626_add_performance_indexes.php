<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chantiers', function (Blueprint $table) {
            $table->index('statut');
        });

        Schema::table('approvisionnements', function (Blueprint $table) {
            $table->index('statutAppro');
            $table->index(['chantier_id', 'statutAppro']);
        });

        Schema::table('recaps_hebdomadaires', function (Blueprint $table) {
            $table->index('statutRecap');
        });

        Schema::table('ouvriers', function (Blueprint $table) {
            $table->index(['chantier_id', 'statutOuvrier']);
        });

        Schema::table('phases', function (Blueprint $table) {
            $table->index('statutPhase');
        });

        Schema::table('taches', function (Blueprint $table) {
            $table->index(['statutTache', 'date_fin_prevue']);
        });

        Schema::table('depenses_chantiers', function (Blueprint $table) {
            $table->index(['chantier_id', 'date_depense']);
        });

        Schema::table('rapports_chantiers', function (Blueprint $table) {
            $table->index('date_rapport');
            $table->index('type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('actif');
        });
    }

    public function down(): void
    {
        Schema::table('chantiers', fn(Blueprint $t) => $t->dropIndex(['statut']));
        Schema::table('approvisionnements', function (Blueprint $t) {
            $t->dropIndex(['statutAppro']);
            $t->dropIndex(['chantier_id', 'statutAppro']);
        });
        Schema::table('recaps_hebdomadaires', fn(Blueprint $t) => $t->dropIndex(['statutRecap']));
        Schema::table('ouvriers', fn(Blueprint $t) => $t->dropIndex(['chantier_id', 'statutOuvrier']));
        Schema::table('phases', fn (Blueprint $t) => $t->dropIndex(['statutPhase']));
        Schema::table('taches', fn(Blueprint $t) => $t->dropIndex(['statutTache', 'date_fin_prevue']));
        Schema::table('depenses_chantiers', fn(Blueprint $t) => $t->dropIndex(['chantier_id', 'date_depense']));
        Schema::table('rapports_chantiers', function (Blueprint $t) {
            $t->dropIndex(['date_rapport']);
            $t->dropIndex(['type']);
        });
        Schema::table('users', fn(Blueprint $t) => $t->dropIndex(['actif']));
    }
};
