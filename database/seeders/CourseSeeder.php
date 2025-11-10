<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Course;
use App\Models\User;
use App\Models\Week;

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
                'name' => 'Pemograman Web Dasar',
                'description' => 'Mempelajari dasar-dasar HTML, CSS, JavaScript, dan PHP.'
            ],
            [
                'user_id' => $pengajar1->id,
                'name' => 'Matematika Komputer',
                'description' => 'Konsep matematika diskrit untuk ilmu komputer.'
            ],
            [
                'user_id' => $pengajar2->id,
                'name' => 'Organisasi dan Arsitektur Komputer',
                'description' => 'Mempelajari arsitektur internal dan organisasi komputer.'
            ],
            [
                'user_id' => $pengajar2->id,
                'name' => 'Game Programming',
                'description' => 'Dasar-dasar pengembangan game menggunakan engine modern.'
            ],
        ];

        $this->command->info('Membuat 4 Course dummy beserta 16 minggu pertemuannya...');

        foreach ($coursesData as $data) {
            
            DB::transaction(function () use ($data) {
                
                $course = Course::create($data);

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