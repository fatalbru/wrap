<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Environment;
use App\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => '',
            'environment' => Environment::TEST,
        ];
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes) => [
            'environment' => Environment::LIVE,
        ]);
    }

    public function order(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::ORDER,
        ]);
    }

    public function subscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::SUBSCRIPTION,
        ]);
    }
}
