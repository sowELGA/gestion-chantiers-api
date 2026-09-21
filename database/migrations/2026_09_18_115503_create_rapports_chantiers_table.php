<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports_chantiers', function (Blueprint $table) {
            $table->id();
            $table->date('date_rapport');
            $table->string('titre')->nullable();
            $table->enum('type', ['avancement', 'incident', 'livraison', 'reunion', 'autre'])->default('avancement');
            $table->text('contenu');
            $table->foreignId('chantier_id')->constrained('chantiers', 'id')->onDelete('cascade');
            $table->foreignId('auteur_id')->constrained('users', 'id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports_chantiers');
    }
};
