<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_chantier', function (Blueprint $table) {
            $table->id();
            $table->date('debut_affectation');
            $table->date('fin_affectation')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chantier_id')->constrained('chantiers')->onDelete('cascade');
            $table->timestamps();

            $table->index(['chantier_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_chantier');
    }
};
