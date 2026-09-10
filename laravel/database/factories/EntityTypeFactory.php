<?php

namespace Database\Factories;

use App\Models\EntityType;
use App\Models\Jurisdiction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EntityType>
 */
class EntityTypeFactory extends Factory
{
    protected $model = EntityType::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'jurisdiction_id' => Jurisdiction::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name, '_'),
            'base_entity' => EntityType::BASE_CONTACT,
            'icon' => 'user',
            'color' => 'blue',
            'description' => null,
            'default_fields' => null,
            'required_fields' => null,
            'is_system' => false,
            'is_active' => true,
        ];
    }

    public function contact(): static
    {
        return $this->state(fn () => [
            'base_entity' => EntityType::BASE_CONTACT,
            'icon' => 'user',
            'color' => 'blue',
        ]);
    }

    public function organization(): static
    {
        return $this->state(fn () => [
            'base_entity' => EntityType::BASE_ORGANIZATION,
            'icon' => 'building',
            'color' => 'green',
        ]);
    }

    public function parish(): static
    {
        return $this->organization()->state(fn () => [
            'name' => 'Parish',
            'slug' => EntityType::SLUG_PARISH,
            'icon' => 'building-2',
            'color' => 'emerald',
            'is_system' => true,
        ]);
    }
}
