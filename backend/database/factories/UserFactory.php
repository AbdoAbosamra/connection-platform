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
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'job_seeker',
            'is_active' => true,
            'phone' => fake()->optional()->e164PhoneNumber(),
            'country' => fake()->countryCode(),
            'timezone' => fake()->timezone(),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function employer(): static
    {
        return $this->state(fn () => ['role' => 'employer']);
    }

    public function jobSeeker(): static
    {
        return $this->state(fn () => ['role' => 'job_seeker']);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
