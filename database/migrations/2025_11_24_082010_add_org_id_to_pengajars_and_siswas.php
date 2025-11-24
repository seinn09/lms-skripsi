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
        Schema::table('pengajars', function (Blueprint $table) {
            $table->foreignId('department_id')
                  ->after('user_id')
                  ->constrained('departments')
                  ->onDelete('cascade');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->foreignId('study_program_id')
                  ->after('user_id')
                  ->constrained('study_programs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajars', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropForeign(['study_program_id']);
            $table->dropColumn('study_program_id');
        });
    }
};