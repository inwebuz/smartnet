<?php

namespace Database\Factories;

use App\Models\AdditionalService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdditionalService>
 */
class AdditionalServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ['ru' => fake()->words(2, true), 'uz' => fake()->words(2, true), 'en' => fake()->words(2, true)],
            'description' => ['ru' => fake()->sentence(), 'uz' => fake()->sentence(), 'en' => fake()->sentence()],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
