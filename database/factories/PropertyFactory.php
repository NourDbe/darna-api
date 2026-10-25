<?php

namespace Database\Factories;

use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(PropertyStatus::cases());

        return [
            'office_id' => Office::query()->inRandomOrder()->value('id'),
            'owner_id' => Owner::query()->inRandomOrder()->value('id'),
            'governorate_id' => Governorate::query()->inRandomOrder()->value('id'),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(PropertyType::cases())->value,
            'listing_type' => fake()->randomElement(ListingType::cases())->value,
            'status' => $status->value,
            'price' => fake()->numberBetween(10000, 500000),
            'currency' => 'USD',
            'is_featured' => fake()->boolean(15),
            'approved_at' => fake()->optional(0.8)->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
