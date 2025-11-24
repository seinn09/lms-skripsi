<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('study_programs')->truncate();
        DB::table('departments')->truncate();
        DB::table('faculties')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info('Membuat Struktur Organisasi Kampus (Fakultas -> Dept -> Prodi)...');
        
        $data = [
            [
                'name' => 'Fakultas Teknik',
                'code' => 'FT',
                'departments' => [
                    [
                        'name' => 'Dept. Teknik Elektro & Informatika',
                        'code' => 'DTEI',
                        'prodis' => [
                            ['name' => 'S1 Teknik Informatika', 'code' => 'TI-S1', 'degree' => 'S1'],
                            ['name' => 'S1 Teknik Elektro', 'code' => 'TE-S1', 'degree' => 'S1'],
                            ['name' => 'D3 Teknik Elektronika', 'code' => 'TE-D3', 'degree' => 'D3'],
                        ]
                    ],
                    [
                        'name' => 'Dept. Teknik Sipil',
                        'code' => 'DTS',
                        'prodis' => [
                            ['name' => 'S1 Teknik Sipil', 'code' => 'TS-S1', 'degree' => 'S1'],
                            ['name' => 'S1 Arsitektur', 'code' => 'ARS-S1', 'degree' => 'S1'],
                            ['name' => 'D3 Teknik Sipil', 'code' => 'TS-D3', 'degree' => 'D3'],
                        ]
                    ],
                    [
                        'name' => 'Dept. Teknik Mesin',
                        'code' => 'DTM',
                        'prodis' => [
                            ['name' => 'S1 Teknik Mesin', 'code' => 'TM-S1', 'degree' => 'S1'],
                            ['name' => 'S1 Otomotif', 'code' => 'OTO-S1', 'degree' => 'S1'],
                            ['name' => 'D3 Teknik Mesin', 'code' => 'TM-D3', 'degree' => 'D3'],
                        ]
                    ],
                ]
            ],

            [
                'name' => 'Fakultas Ekonomi dan Bisnis',
                'code' => 'FEB',
                'departments' => [
                    [
                        'name' => 'Dept. Manajemen',
                        'code' => 'DM',
                        'prodis' => [
                            ['name' => 'S1 Manajemen', 'code' => 'MNJ-S1', 'degree' => 'S1'],
                            ['name' => 'S1 Bisnis Digital', 'code' => 'BD-S1', 'degree' => 'S1'],
                            ['name' => 'S2 Manajemen', 'code' => 'MNJ-S2', 'degree' => 'S2'],
                        ]
                    ],
                    [
                        'name' => 'Dept. Akuntansi',
                        'code' => 'DAK',
                        'prodis' => [
                            ['name' => 'S1 Akuntansi', 'code' => 'AKT-S1', 'degree' => 'S1'],
                            ['name' => 'D3 Akuntansi', 'code' => 'AKT-D3', 'degree' => 'D3'],
                            ['name' => 'Pendidikan Profesi Akuntan', 'code' => 'PPAk', 'degree' => 'Profesi'],
                        ]
                    ],
                    [
                        'name' => 'Dept. Ekonomi Pembangunan',
                        'code' => 'DEP',
                        'prodis' => [
                            ['name' => 'S1 Ekonomi Pembangunan', 'code' => 'EKP-S1', 'degree' => 'S1'],
                            ['name' => 'S1 Ekonomi Islam', 'code' => 'EKI-S1', 'degree' => 'S1'],
                            ['name' => 'S2 Ilmu Ekonomi', 'code' => 'IE-S2', 'degree' => 'S2'],
                        ]
                    ],
                ]
            ],

            [
                'name' => 'Fakultas Ilmu Pendidikan',
                'code' => 'FIP',
                'departments' => [
                    [
                        'name' => 'Dept. Teknologi Pendidikan',
                        'code' => 'DTP',
                        'prodis' => [
                            ['name' => 'S1 Teknologi Pendidikan', 'code' => 'TP-S1', 'degree' => 'S1'],
                            ['name' => 'S2 Teknologi Pembelajaran', 'code' => 'TP-S2', 'degree' => 'S2'],
                            ['name' => 'S3 Teknologi Pembelajaran', 'code' => 'TP-S3', 'degree' => 'S3'],
                        ]
                    ],
                    [
                        'name' => 'Dept. Pendidikan Luar Biasa',
                        'code' => 'DPLB',
                        'prodis' => [
                            ['name' => 'S1 Pendidikan Luar Biasa', 'code' => 'PLB-S1', 'degree' => 'S1'],
                            ['name' => 'S2 Pendidikan Khusus', 'code' => 'PK-S2', 'degree' => 'S2'],
                            ['name' => 'S1 Pendidikan Inklusi', 'code' => 'PI-S1', 'degree' => 'S1'],
                        ]
                    ],
                    [
                        'name' => 'Dept. Administrasi Pendidikan',
                        'code' => 'DAP',
                        'prodis' => [
                            ['name' => 'S1 Administrasi Pendidikan', 'code' => 'AP-S1', 'degree' => 'S1'],
                            ['name' => 'S2 Manajemen Pendidikan', 'code' => 'MP-S2', 'degree' => 'S2'],
                            ['name' => 'S3 Manajemen Pendidikan', 'code' => 'MP-S3', 'degree' => 'S3'],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($data as $facData) {
            $faculty = Faculty::create([
                'name' => $facData['name'],
                'code' => $facData['code'],
            ]);

            foreach ($facData['departments'] as $deptData) {
                $department = Department::create([
                    'faculty_id' => $faculty->id,
                    'name' => $deptData['name'],
                    'code' => $deptData['code'],
                ]);

                foreach ($deptData['prodis'] as $prodiData) {
                    StudyProgram::create([
                        'department_id' => $department->id,
                        'name' => $prodiData['name'],
                        'code' => $prodiData['code'],
                        'degree' => $prodiData['degree'],
                    ]);
                }
            }
        }

        $this->command->info('Selesai! 3 Fakultas, 9 Departemen, dan 27 Prodi telah dibuat.');
    }
}