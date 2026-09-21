<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ouvriers', function (Blueprint $table) {
            $table->id();
            $table->string('nomOuvrier');
            $table->string('prenomOuvrier');
            $table->string('telOuvrier');
            $table->enum('statutOuvrier', ['actif', 'inactif'])->default('actif');
            $table->foreignId('poste_id')->nullable()->constrained('postes', 'id')->nullOnDelete();
            $table->foreignId('chantier_id')->nullable()->constrained('chantiers', 'id')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ouvriers');
    }
};
