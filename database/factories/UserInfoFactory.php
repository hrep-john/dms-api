<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->lastName(),
            'last_name' => $this->faker->lastName(),
            'mobile_number' => $this->faker->numerify('###########'),
            'sex' => $this->faker->rand(0, 1) ? 'Male' : false,
            'profile_picture_url' => 'https://via.placeholder.com/150x150',
            'home_address' => $this->faker->streetAddress(),
            'barangay' => $this->faker->streetAddress(),
            'city' => $this->faker->streetAddress(),
            'region' => $this->faker->streetAddress(),
        ];
    }
}
