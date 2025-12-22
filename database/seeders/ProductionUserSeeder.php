<?php

namespace Database\Seeders;

use App\Models\Pengajar;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\StaffProdi;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ProductionUserSeeder extends Seeder
{
    /**
     * Run the database seeds for PRODUCTION (no Faker dependency).
     */
    public function run(): void
    {
        $this->truncateUserTables();

        $this->command->info('Membuat 9 Akun Pengguna...');

        // Simpan tenant_id saat ini untuk direstore nanti
        $currentTenantId = session('tenant_id');

        // Hapus tenant_id dari session agar superadmin tidak punya tenant
        session()->forget('tenant_id');

        // 1. Superadministrator
        $superadmin = User::create([
            'name' => 'Superadministrator',
            'email' => 'superadministrator@app.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'label' => 'superadministrator',
        ]);

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'superadministrator'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Manajemen penuh sistem'
            ]
        );
        $superadmin->addRole($superAdminRole);

        // Restore tenant_id untuk user lainnya
        session(['tenant_id' => $currentTenantId]);

        // 2-3. Admin
        $admin1 = User::create([
            'name' => 'Admin 1',
            'email' => 'admin1@app.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'label' => 'admin',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrator',
            'description' => 'admin sistem (tidak memiliki akses manajemen penuh seperti superadministrator)'
        ]);
        $admin1->addRole($adminRole);

        $admin2 = User::create([
            'name' => 'Admin 2',
            'email' => 'admin2@app.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'label' => 'admin',
        ]);
        $admin2->addRole($adminRole);

        // 4-5. Staff Prodi
        $staffRole = Role::firstOrCreate(['name' => 'staff_prodi'], [
            'display_name' => 'Staff Prodi',
            'description' => 'Admin tingkat Program Studi'
        ]);

        $staffTI = User::create([
            'name' => 'Staff TI',
            'email' => 'staff_ti@app.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'label' => 'staff_prodi',
        ]);
        $staffTI->addRole($staffRole);

        $prodiTI = StudyProgram::where('code', 'TI-S1')->first() ?? StudyProgram::first();
        if ($prodiTI) {
            StaffProdi::create([
                'user_id' => $staffTI->id,
                'study_program_id' => $prodiTI->id,
                'nip' => '198501011234567890',
            ]);
        }

        $staffTE = User::create([
            'name' => 'Staff Elektro',
            'email' => 'staff_te@app.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'label' => 'staff_prodi',
        ]);
        $staffTE->addRole($staffRole);

        $prodiTE = StudyProgram::where('code', 'TE-S1')->first() ?? StudyProgram::skip(1)->first();
        if ($prodiTE) {
            StaffProdi::create([
                'user_id' => $staffTE->id,
                'study_program_id' => $prodiTE->id,
                'nip' => '198601011234567891',
            ]);
        }

        // 6-7. Pengajar
        $pengajarRole = Role::firstOrCreate(['name' => 'pengajar'], [
            'display_name' => 'Pengajar',
            'description' => 'Manajemen materi ajar'
        ]);

        $pengajar1 = User::create([
            'name' => 'Pengajar 1',
            'email' => 'pengajar1@app.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'label' => 'pengajar',
        ]);
        $pengajar1->addRole($pengajarRole);

        if ($prodiTI) {
            Pengajar::create([
                'user_id' => $pengajar1->id,
                'study_program_id' => $prodiTI->id,
                'nip' => '199001011234567892',
                'alamat' => 'Jl. Contoh No. 1',
                'tanggal_lahir' => '1990-01-01',
            ]);
        }

        $pengajar2 = User::create([
            'name' => 'Pengajar 2',
            'email' => 'pengajar2@app.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'label' => 'pengajar',
        ]);
        $pengajar2->addRole($pengajarRole);

        if ($prodiTI) {
            Pengajar::create([
                'user_id' => $pengajar2->id,
                'study_program_id' => $prodiTI->id,
                'nip' => '199101011234567893',
                'alamat' => 'Jl. Contoh No. 2',
                'tanggal_lahir' => '1991-01-01',
            ]);
        }

        // 8-9. Siswa/Mahasiswa
        $siswaRole = Role::firstOrCreate(['name' => 'siswa'], [
            'display_name' => 'Siswa',
            'description' => 'Akses materi ajar'
        ]);

        $mahasiswa1 = User::create([
            'name' => 'Mahasiswa 1',
            'email' => 'mahasiswa1@app.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'label' => 'siswa',
        ]);
        $mahasiswa1->addRole($siswaRole);

        if ($prodiTI) {
            Siswa::create([
                'user_id' => $mahasiswa1->id,
                'study_program_id' => $prodiTI->id,
                'nim' => '2205350001',
                'alamat' => 'Jl. Mahasiswa No. 1',
                'tanggal_lahir' => '2004-01-01',
            ]);
        }

        $mahasiswa2 = User::create([
            'name' => 'Mahasiswa 2',
            'email' => 'mahasiswa2@app.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'label' => 'siswa',
        ]);
        $mahasiswa2->addRole($siswaRole);

        $prodiForMhs2 = $prodiTE ?? $prodiTI;
        if ($prodiForMhs2) {
            Siswa::create([
                'user_id' => $mahasiswa2->id,
                'study_program_id' => $prodiForMhs2->id,
                'nim' => '2205350002',
                'alamat' => 'Jl. Mahasiswa No. 2',
                'tanggal_lahir' => '2004-02-01',
            ]);
        }

        $this->command->info('9 Akun Pengguna (Superadmin, Admin x2, Staff Prodi x2, Pengajar x2, Mahasiswa x2) berhasil dibuat.');
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
