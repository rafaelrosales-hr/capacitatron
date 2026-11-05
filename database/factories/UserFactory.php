<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'curp' => Str::upper(Str::random(18)),
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'second_surname' => fake()->lastName(),
            'state' => fake()->state(),
            'municipality' => fake()->city(),
            'ocupation' => fake()->jobTitle(),
            'education_level' => 'LICENCIATURA',
            'major_title' => fake()->jobTitle(),
            'institution' => 'PÚBLICA',
            'workplace' => 'HR Media S.A de C.V',
            'area' => fake()->word(),
            'major' => fake()->word(),
            'major_description' => fake()->sentence(3),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
