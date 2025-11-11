<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Week;
use App\Models\Course;
use App\Models\CourseClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTables();
        $this->command->info('Mencari data pengajar...');

        $pengajar1 = User::where('email', 'pengajar1@app.com')->first();
        $pengajar2 = User::where('email', 'pengajar2@app.com')->first();

        if (!$pengajar1 || !$pengajar2) {
            $this->command->error('Seeder Pengguna (UserSeeder) belum dijalankan.');
            $this->command->warn('Silakan jalankan "php artisan db:seed" terlebih dahulu.');
            return;
        }

        $coursesData = [
            [
                'user_id' => $pengajar1->id,
                'course_code' => 'NINFUM6039',
                'name' => 'Pemograman Web Dasar',
                'description' => 'Mempelajari dasar-dasar HTML, CSS, JavaScript, dan PHP.'
            ],
            [
                'user_id' => $pengajar1->id,
                'course_code' => 'NINFUM6040',
                'name' => 'Matematika Komputer',
                'description' => 'Konsep matematika diskrit untuk ilmu komputer.'
            ],
            [
                'user_id' => $pengajar2->id,
                'course_code' => 'NINFUM6041',
                'name' => 'Organisasi dan Arsitektur Komputer',
                'description' => 'Mempelajari arsitektur internal dan organisasi komputer.'
            ],
            [
                'user_id' => $pengajar2->id,
                'course_code' => 'NINFUM6042',
                'name' => 'Game Programming',
                'description' => 'Dasar-dasar pengembangan game menggunakan engine modern.'
            ],
        ];

        $this->command->info('Membuat 4 Course dummy beserta 16 minggu pertemuannya...');

        foreach ($coursesData as $data) {
            
            DB::transaction(function () use ($data, $pengajar1, $pengajar2) {

                $course = Course::create($data);

                $pengajarKelas = ($course->user_id == $pengajar1->id) ? $pengajar1 : $pengajar2;
                
                CourseClass::create([
                    'course_id' => $course->id,
                    'user_id' => $pengajarKelas->id,
                    'class_code' => $course->course_code . '-A',
                    'semester' => 'Ganjil 2025/2026',
                    'capacity' => 40,
                    'status' => 'open',
                ]);

                for ($i = 1; $i <= 16; $i++) {
                    Week::create([
                        'course_id' => $course->id,
                        'week_number' => $i,
                        'title' => "Pertemuan Ke-$i",
                        'description' => "Materi untuk pertemuan minggu ke-$i akan diisi oleh dosen."
                    ]);
                }
            });
        }
        $this->command->info('CourseSeeder selesai dijalankan.');
    }

    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('materials')->truncate();
        DB::table('weeks')->truncate();
        DB::table('courses')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}