<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Pengajar;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
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

    public function superAdministrator(): static
    {
        return $this->afterCreating(function (User $user) {
            $superAdminRole = Role::firstOrCreate(
                ['name' => 'superadministrator'],
                [
                    'display_name' => 'Super Administrator',
                    'description' => 'Manajemen penuh sistem'
                ]
            );

            $user->addRole($superAdminRole);
            $user->update(['label' => 'superadministrator']);
        });
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'admin'], [
                'display_name' => 'Administrator',
                'description' => 'admin sistem (tidak memiliki akses manajemen penuh seperti superadministrator)'
            ]);
            $user->addRole($role);
            $user->update(['label' => 'admin']);
        });
    }

    public function pengajar(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'pengajar'], [
                'display_name' => 'Pengajar',
                'description' => 'Pengajar atau dosen yang mengelola pelajaran'
            ]);
            $user->addRole($role);
            $user->update(['label' => 'pengajar']);

            Pengajar::create(['user_id' => $user->id]);
        });
    }

    public function siswa(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'siswa'], [
                'display_name' => 'Siswa',
                'description' => 'Siswa atau mahasiswa yang mengikuti pelajaran'
            ]);
            $user->addRole($role);
            $user->update(['label' => 'siswa']);

            Siswa::create(['user_id' => $user->id]);
        });
    }
}
