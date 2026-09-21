<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();       // slug : admin, directeur_travaux, daf, responsable_rh, chef_projet, pointeur
            $table->string('libelle');              // nom affiché : "Administrateur", "Directeur des travaux"...
            $table->boolean('exclusif')->default(false); // true pour chef_projet et pointeur
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
