<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'faculties', 'departments', 'study_programs',
        'staff_prodis', 'pengajars', 'siswas',
        'courses', 'course_classes', 'course_student',
        'weeks', 'materials', 'assignments', 'assignment_submissions',
        'exams', 'questions', 'options', 'exam_questions', 'exam_attempts', 'exam_answers',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('tenant_id')->nullable()->index();
                $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
