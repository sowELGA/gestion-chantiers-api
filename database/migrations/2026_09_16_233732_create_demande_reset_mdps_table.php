<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_reset_mdps', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->enum('statut', ['en_attente', 'traitee'])->default('en_attente');
            $table->timestamp('traitee_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_reset_mdps');
    }
};
