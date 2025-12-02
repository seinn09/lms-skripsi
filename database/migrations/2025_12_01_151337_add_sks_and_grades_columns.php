<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('sks')->default(3)->after('name')
                  ->comment('Bobot SKS Mata Kuliah');
        });

        Schema::table('course_student', function (Blueprint $table) {
            $table->float('final_score')->nullable()->comment('Nilai Angka (0-100)');
            $table->string('final_grade', 5)->nullable()->comment('Nilai Huruf (A, B, C)');
            $table->float('grade_point')->nullable()->comment('Bobot Nilai (4.0, 3.0)');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('sks');
        });

        Schema::table('course_student', function (Blueprint $table) {
            $table->dropColumn(['final_score', 'final_grade', 'grade_point']);
        });
    }
};