<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('tenants')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info('Membuat Tenant (Kampus)...');

        Tenant::create([
            'tenant_id' => 'univ-merdeka',
            'name' => 'Universitas Merdeka Belajar',
            'slug' => 'univ-merdeka',
            'address' => 'Jl. Pendidikan No. 1, Jakarta',
            'email' => 'info@univ-merdeka.ac.id',
            'phone' => '021-1234567',
        ]);

        Tenant::create([
            'tenant_id' => 'institut-teknologi',
            'name' => 'Institut Teknologi Canggih',
            'slug' => 'itc',
        ]);
    }
}
