<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaratrustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateLaratrustTables();

        $roles = [
            'superadministrator',
            'admin',
            'pengajar',
            'siswa'
        ];

        foreach ($roles as $roleName) {
            Role::create([
                'name' => $roleName,
                'display_name' => ucwords(str_replace('_', ' ', $roleName)),
                'description' => 'Role sebagai ' . ucwords(str_replace('_', ' ', $roleName)),
            ]);
        }

        $this->command->info('4 Roles (superadministrator, admin, pengajar, siswa) berhasil dibuat.');
    }

    public function truncateLaratrustTables()
    {
        $this->command->info('Menghapus isi tabel Laratrust (roles, permissions)...');
        Schema::disableForeignKeyConstraints();

        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();

        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
