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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_student_id')->constrained('group_students')->onDelete('cascade');;
            $table->enum('exam_type',['Oral','Written']);
            $table->integer('Written_Test')->nullable();
            $table->integer('Oral_Test')->nullable();
            $table->integer('Attendance')->nullable();
            $table->integer('Participation')->nullable();
            $table->integer('Home_Work')->nullable();
            $table->integer('Communication')->nullable();
            $table->integer('Vocabulary')->nullable();
            $table->integer('Structure')->nullable();
            $table->integer('Mark');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
