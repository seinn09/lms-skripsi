<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Pengajar;
use App\Models\Department;
use App\Models\StaffProdi;
use Illuminate\Support\Str;
use App\Models\StudyProgram;
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

    public function staffProdi(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'staff_prodi'], [
                'display_name' => 'Staff Prodi', 'description' => 'Admin tingkat Program Studi'
            ]);
            $user->addRole($role);
            $user->update(['label' => 'staff_prodi']);

            $prodi = StudyProgram::inRandomOrder()->first() ?? StudyProgram::factory()->create();

            StaffProdi::create([
                'user_id' => $user->id,
                'study_program_id' => $prodi->id,
                'nip' => fake()->unique()->numerify('198#######1#######'),
            ]);
        });
    }

    public function pengajar(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'pengajar'], [
                'display_name' => 'Pengajar', 'description' => 'Manajemen materi ajar'
            ]);
            $user->addRole($role);
            $user->update(['label' => 'pengajar']);

            $dept = Department::inRandomOrder()->first() ?? Department::factory()->create();

            Pengajar::create([
                'user_id' => $user->id,
                'department_id' => $dept->id,
                'nip' => fake()->unique()->numerify('199#######1#######'),
                'alamat' => fake()->address(),
                'tanggal_lahir' => fake()->date(),
            ]);
        });
    }

    public function siswa(): static
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'siswa'], [
                'display_name' => 'Siswa', 'description' => 'Akses materi ajar'
            ]);
            $user->addRole($role);
            $user->update(['label' => 'siswa']);
            
            $prodi = StudyProgram::inRandomOrder()->first() ?? StudyProgram::factory()->create();

            Siswa::create([
                'user_id' => $user->id,
                'study_program_id' => $prodi->id,
                'nim' => fake()->unique()->numerify('220535######'),
                'alamat' => fake()->address(),
                'tanggal_lahir' => fake()->date(),
            ]);
        });
    }
}
