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
        Schema::create('weeks', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->onDelete('cascade');

            $table->integer('week_number');
            $table->string('title');
            $table->text('description')->nullable();

            $table->timestamps();
            
            $table->unique(['course_id', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weeks');
    }
};