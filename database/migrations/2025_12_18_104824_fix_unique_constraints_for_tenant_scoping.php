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
        // Drop old unique constraints and add composite unique constraints for tenant scoping

        // Faculties: drop unique on code, add composite unique on (tenant_id, code)
        Schema::table('faculties', function (Blueprint $table) {
            $table->dropUnique(['code']); // Drop the old unique constraint
            $table->unique(['tenant_id', 'code'], 'faculties_tenant_code_unique'); // Add composite unique
        });

        // Departments: drop unique on code, add composite unique on (tenant_id, code)
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique(['code']); // Drop the old unique constraint
            $table->unique(['tenant_id', 'code'], 'departments_tenant_code_unique'); // Add composite unique
        });

        // Study Programs: if there's a unique constraint on code, drop it and add composite
        // Note: study_programs might not have a unique constraint on code, so we'll check
        Schema::table('study_programs', function (Blueprint $table) {
            // If study_programs has a unique constraint on code, uncomment the following:
            // $table->dropUnique(['code']);
            // $table->unique(['tenant_id', 'code'], 'study_programs_tenant_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original unique constraints

        Schema::table('faculties', function (Blueprint $table) {
            $table->dropUnique('faculties_tenant_code_unique');
            $table->unique('code');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique('departments_tenant_code_unique');
            $table->unique('code');
        });

        Schema::table('study_programs', function (Blueprint $table) {
            // If we added a composite unique in up(), reverse it here:
            // $table->dropUnique('study_programs_tenant_code_unique');
            // $table->unique('code');
        });
    }
};
