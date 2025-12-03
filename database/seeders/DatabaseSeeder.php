<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    // public function run(): void
    // {
    //     // \App\Models\User::factory(10)->create();

    //     // \App\Models\User::factory()->create([
    //     //     'name' => 'Test User',
    //     //     'email' => 'test@example.com',
    //     // ]);

    //     $this->call([
    //         LaratrustSeeder::class,
    //         OrganizationSeeder::class,
    //         UserSeeder::class,
    //         CourseSeeder::class,
    //         ExamSeeder::class,
    //     ]);
    // }

    public function run(): void
    {
        $this->call(LaratrustSeeder::class);

        $this->call(TenantSeeder::class);

        session(['tenant_id' => 'univ-merdeka']);
        $this->command->info('✅ Konteks Tenant di-set ke: univ-merdeka');

        $this->call([
            OrganizationSeeder::class,
            UserSeeder::class,
            CourseSeeder::class,
            ExamSeeder::class,
        ]);

        session()->forget('tenant_id');
    }
}
