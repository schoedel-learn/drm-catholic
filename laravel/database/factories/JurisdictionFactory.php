<?php

namespace Database\Factories;

use App\Models\Jurisdiction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jurisdiction>
 */
class JurisdictionFactory extends Factory
{
    protected $model = Jurisdiction::class;

    public function definition(): array
    {
        return [
            'name' => 'Diocese of '.$this->faker->city(),
            'type' => 'diocese',
            'province' => '',
            'state' => $this->faker->stateAbbr(),
            'city' => $this->faker->city(),
            'established' => null,
            'bishop' => null,
            'website' => null,
            'email' => null,
            'phone' => null,
            'address' => null,
            'is_external' => false,
            'locked' => false,
        ];
    }
}
