<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->string('day')->nullable()->after('semester')->comment('Senin, Selasa, dst');
            $table->time('time_start')->nullable()->after('day');
            $table->time('time_end')->nullable()->after('time_start');
        });
    }

    public function down(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropColumn(['day', 'time_start', 'time_end']);
        });
    }
};