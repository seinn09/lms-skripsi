<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateUserTables();

        $this->command->info('Membuat Super Administrator...');

        User::factory()->superAdministrator()->create([
            'name' => 'Superadministrator',
            'email' => 'superadministrator@lms.test',
            'password' => bcrypt('password'),
        ]);
        
        $this->command->info('Super Administrator berhasil dibuat.');

        // Nanti Anda bisa tambahkan seeder user lain di sini
        // User::factory(10)->create(); // Contoh
    }

    public function truncateUserTables()
    {
        $this->command->info('Truncating User and related pivot tables');
        Schema::disableForeignKeyConstraints();

        DB::table('role_user')->truncate();
        DB::table('permission_user')->truncate();
        
        $usersTable = (new User)->getTable(); 
        DB::table($usersTable)->truncate();
        
        Schema::enableForeignKeyConstraints();
        $this->command->info('User tables truncated.');
    }
}
