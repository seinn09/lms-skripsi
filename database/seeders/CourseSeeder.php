<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Course;
use App\Models\User;
use App\Models\Week;
use App\Models\CourseClass;
use App\Models\StudyProgram; // <-- IMPORT INI

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncateTables();
        $this->command->info('Mencari data pendukung...');

        $pengajar1 = User::where('email', 'pengajar1@app.com')->first();
        $pengajar2 = User::where('email', 'pengajar2@app.com')->first();

        $prodiTI = StudyProgram::where('code', 'TI-S1')->first();

        if (!$pengajar1 || !$pengajar2 || !$prodiTI) {
            $this->command->error('Data Pengajar atau Prodi tidak ditemukan. Pastikan UserSeeder & OrganizationSeeder sudah jalan.');
            return;
        }

        $coursesData = [
            [
                'study_program_id' => $prodiTI->id,
                'user_id' => $pengajar1->id,
                'course_code' => 'NINFUM6039',
                'name' => 'Pemograman Web Dasar',
                'description' => 'Mempelajari dasar-dasar HTML, CSS, JavaScript, dan PHP.'
            ],
            [
                'study_program_id' => $prodiTI->id,
                'user_id' => $pengajar1->id,
                'course_code' => 'NINFUM6012',
                'name' => 'Matematika Komputer',
                'description' => 'Konsep matematika diskrit untuk ilmu komputer.'
            ],
            [
                'study_program_id' => $prodiTI->id,
                'user_id' => $pengajar2->id,
                'course_code' => 'NINFUM6025',
                'name' => 'Organisasi dan Arsitektur Komputer',
                'description' => 'Mempelajari arsitektur internal dan organisasi komputer.'
            ],
            [
                'study_program_id' => $prodiTI->id,
                'user_id' => $pengajar2->id,
                'course_code' => 'NINFUM6044',
                'name' => 'Game Programming',
                'description' => 'Dasar-dasar pengembangan game menggunakan engine modern.'
            ],
        ];

        $this->command->info('Membuat Course...');

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
                        'description' => "Materi minggu ke-$i."
                    ]);
                }
            });
        }
        
        $this->command->info('CourseSeeder selesai.');
    }

    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('course_student')->truncate();
        DB::table('course_classes')->truncate();
        DB::table('assignment_submissions')->truncate();
        DB::table('assignments')->truncate();
        DB::table('materials')->truncate();
        DB::table('weeks')->truncate();
        DB::table('courses')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}