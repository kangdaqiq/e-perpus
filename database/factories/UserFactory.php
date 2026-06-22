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
     * Menggunakan kolom yang sesuai dengan skema tabel users E-Perpus.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name'     => fake()->name(),
            'username'      => fake()->unique()->userName(),
            'email'         => fake()->unique()->safeEmail(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'role'          => 'teacher',
            'school_id'     => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Jadikan user sebagai admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Jadikan user sebagai super_admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
        ]);
    }
}
