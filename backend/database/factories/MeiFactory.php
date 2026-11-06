<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Mei;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mei>
 */
class MeiFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cnpj' => fake()->unique()->numerify('##############'),
            'mei_name' => fake()->company(),
            'opening_date' => fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'ibge_municipality_code' => fake()->numerify('#######'),
            'user_id' => User::factory(),
        ];
    }
}
