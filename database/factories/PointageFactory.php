<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointageFactory extends Factory
{
    use HasFactory;

    public function definition(): array
    {
        return [
            'date' => now(),
            'statutPointage' => 'present',
            'heures_sup' => 0,
        ];
    }
}
