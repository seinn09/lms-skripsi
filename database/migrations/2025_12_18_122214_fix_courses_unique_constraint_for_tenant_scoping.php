<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing global unique constraint on course_code
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique(['course_code']);
        });

        // Add a composite unique constraint on (tenant_id, course_code)
        Schema::table('courses', function (Blueprint $table) {
            $table->unique(['tenant_id', 'course_code'], 'courses_tenant_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the composite unique constraint
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique('courses_tenant_code_unique');
        });

        // Restore the global unique constraint on course_code
        Schema::table('courses', function (Blueprint $table) {
            $table->unique('course_code');
        });
    }
};
