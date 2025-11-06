<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LaratrustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->truncateLaratrustTables();

        $config = Config::get('laratrust_seeder.roles_structure');
        $mapPermission = collect(config('laratrust_seeder.permissions_map'));

        foreach ($config as $roleName => $modules) {
            
            $role = Role::create([
                'name' => $roleName,
                'display_name' => ucwords(str_replace('_', ' ', $roleName)),
                'description' => 'Role sebagai ' . ucwords(str_replace('_', ' ', $roleName)),
            ]);
            
            $permissions = [];
            $this->command->info('Membuat Role: ' . strtoupper($roleName));

            foreach ($modules as $module => $value) {

                foreach (explode(',', $value) as $perm) {

                    $permissionValue = $mapPermission->get($perm);
                    $permissionName = $module . '-' . $permissionValue;

                    $permissions[] = Permission::firstOrCreate([
                        'name' => $permissionName,
                        'display_name' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                        'description' => 'Izin untuk ' . ucfirst($permissionValue) . ' ' . ucfirst($module),
                    ])->id;

                    $this->command->info('  -- Membuat Permission: ' . $permissionName);
                }
            }

            $role->permissions()->sync($permissions);
        }
    }

    /**
     * Truncates all the Laratrust tables
     *
     * @return  void
     */
    public function truncateLaratrustTables()
    {
        $this->command->info('Mengosongkan tabel Laratrust...');
        Schema::disableForeignKeyConstraints();

        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();
        
        if (Config::get('laratrust_seeder.truncate_tables', true)) {
            DB::table('roles')->truncate();
            DB::table('permissions')->truncate();
        }

        Schema::enableForeignKeyConstraints();
    }
}
