<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Course;
use App\Models\Week;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Option;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->truncateTables();
        $this->command->info('Membuat Bank Soal dan Ujian...');

        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->error('Tidak ada mata kuliah ditemukan. Jalankan CourseSeeder terlebih dahulu.');
            return;
        }

        foreach ($courses as $course) {
            $this->command->info("Memproses Matkul: {$course->name}");
            
            // A. Buat 15 Soal Pilihan Ganda
            $mcQuestions = [];
            for ($i = 1; $i <= 15; $i++) {
                $q = Question::create([
                    'course_id' => $course->id,
                    'type' => 'multiple_choice',
                    'question_text' => "Pertanyaan Pilihan Ganda No. $i untuk mata kuliah {$course->name}?",
                    'weight' => 5,
                ]);

                Option::create(['question_id' => $q->id, 'option_text' => 'Pilihan Jawaban A (Salah)', 'is_correct' => false]);
                Option::create(['question_id' => $q->id, 'option_text' => 'Pilihan Jawaban B (Benar)', 'is_correct' => true]);
                Option::create(['question_id' => $q->id, 'option_text' => 'Pilihan Jawaban C (Salah)', 'is_correct' => false]);
                Option::create(['question_id' => $q->id, 'option_text' => 'Pilihan Jawaban D (Salah)', 'is_correct' => false]);

                $mcQuestions[] = $q->id;
            }

            // B. Buat 5 Soal Esai
            $essayQuestions = [];
            for ($i = 1; $i <= 5; $i++) {
                $q = Question::create([
                    'course_id' => $course->id,
                    'type' => 'essay',
                    'question_text' => "Jelaskan secara detail mengenai topik $i pada mata kuliah {$course->name}!",
                    'weight' => 10,
                ]);
                $essayQuestions[] = $q->id;
            }

            // Skenario A: KUIS (Minggu ke-4)
            $week4 = Week::where('course_id', $course->id)->where('week_number', 4)->first();
            if ($week4) {
                $quiz = Exam::create([
                    'week_id' => $week4->id,
                    'title' => "Kuis 1: Evaluasi Awal",
                    'description' => "Kerjakan kuis ini untuk menguji pemahaman materi minggu 1-3.",
                    'duration_minutes' => 30,
                    'start_time' => now()->subDays(2),
                    'end_time' => now()->addDays(5),
                ]);

                $order = 1;
                foreach (collect($mcQuestions)->take(5) as $qId) {
                    $quiz->questions()->attach($qId, ['order' => $order++]);
                }
            }

            // Skenario B: UTS (Minggu ke-8)
            $week8 = Week::where('course_id', $course->id)->where('week_number', 8)->first();
            if ($week8) {
                $uts = Exam::create([
                    'week_id' => $week8->id,
                    'title' => "Ujian Tengah Semester (UTS)",
                    'description' => "Wajib dikerjakan. Mencakup materi paruh pertama semester.",
                    'duration_minutes' => 90,
                    'start_time' => now()->addWeeks(1),
                    'end_time' => now()->addWeeks(1)->addHours(4),
                ]);

                $order = 1;
                foreach (collect($mcQuestions)->take(10) as $qId) {
                    $uts->questions()->attach($qId, ['order' => $order++]);
                }
                foreach (collect($essayQuestions)->take(2) as $qId) {
                    $uts->questions()->attach($qId, ['order' => $order++]);
                }
            }

            // Skenario C: UAS (Minggu ke-16)
            $week16 = Week::where('course_id', $course->id)->where('week_number', 16)->first();
            if ($week16) {
                $uas = Exam::create([
                    'week_id' => $week16->id,
                    'title' => "Ujian Akhir Semester (UAS)",
                    'description' => "Ujian komprehensif akhir semester.",
                    'duration_minutes' => 120,
                    'start_time' => now()->addWeeks(8),
                    'end_time' => now()->addWeeks(8)->addHours(5),
                ]);

                $order = 1;
                foreach ($mcQuestions as $qId) {
                    $uas->questions()->attach($qId, ['order' => $order++]);
                }
                // Semua Esai
                foreach ($essayQuestions as $qId) {
                    $uas->questions()->attach($qId, ['order' => $order++]);
                }
            }
        }
    }

    public function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('exam_answers')->truncate();
        DB::table('exam_attempts')->truncate();
        DB::table('exam_questions')->truncate();
        DB::table('options')->truncate();
        DB::table('questions')->truncate();
        DB::table('exams')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}