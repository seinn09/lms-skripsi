<?php

namespace Database\Seeders;

use App\Models\Pengajar;
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

        $this->command->info('Membuat 9 Akun Pengguna...');

        // Simpan tenant_id saat ini untuk direstore nanti
        $currentTenantId = session('tenant_id');

        // Hapus tenant_id dari session agar superadmin tidak punya tenant
        session()->forget('tenant_id');

        User::factory()->superAdministrator()->create([
            'name' => 'Superadministrator',
            'email' => 'superadministrator@app.com',
            'password' => bcrypt('password'),
        ]);

        // Restore tenant_id untuk user lainnya
        session(['tenant_id' => $currentTenantId]);

        #AKUN UNTUK ADMIN
        User::factory()->admin()->create([
            'name' => 'Admin 1',
            'email' => 'admin1@app.com',
            'password' => bcrypt('password'),
        ]);
        User::factory()->admin()->create([
            'name' => 'Admin 2',
            'email' => 'admin2@app.com',
            'password' => bcrypt('password'),
        ]);

        #AKUN UNTUK STAFF PRODI
        User::factory()->staffProdi()->create([
            'name' => 'Staff TI',
            'email' => 'staff_ti@app.com',
            'password' => bcrypt('password'),
        ]);
        User::factory()->staffProdi()->create([
            'name' => 'Staff Elektro',
            'email' => 'staff_te@app.com',
            'password' => bcrypt('password'),
        ]);

        #AKUN UNTUK PENGAJAR
        User::factory()->pengajar()->create([
            'name' => 'Pengajar 1',
            'email' => 'pengajar1@app.com',
            'password' => bcrypt('password'),
        ]);
        User::factory()->pengajar()->create([
            'name' => 'Pengajar 2',
            'email' => 'pengajar2@app.com',
            'password' => bcrypt('password'),
        ]);

        #AKUN UNTUK SISWA
        User::factory()->siswa()->create([
            'name' => 'Mahasiswa 1',
            'email' => 'mahasiswa1@app.com',
            'password' => bcrypt('password'),
        ]);
        User::factory()->siswa()->create([
            'name' => 'Mahasiswa 2',
            'email' => 'mahasiswa2@app.com',
            'password' => bcrypt('password'),
        ]);

        $this->command->info('9 Akun Pengguna (Superadmin, Admin x2, Pengajar x2, Siswa x2) berhasil dibuat.');
    }

    public function truncateUserTables()
    {
        $this->command->info('Truncating User and related pivot tables');
        Schema::disableForeignKeyConstraints();

        DB::table('role_user')->truncate();
        DB::table('permission_user')->truncate();

        DB::table('pengajars')->truncate();
        DB::table('siswas')->truncate();
        DB::table('staff_prodis')->truncate();

        $usersTable = (new User)->getTable();
        DB::table($usersTable)->truncate();

        Schema::enableForeignKeyConstraints();
        $this->command->info('User tables truncated.');
    }
}
