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
        Schema::create('kids_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_student_id')->constrained('group_students')->onDelete('cascade');;
            $table->enum('Communication',['Fair','Good','Very Good','Excellent']);
            $table->enum('Vocabulary',['Fair','Good','Very Good','Excellent']);
            $table->enum('Structure',['Fair','Good','Very Good','Excellent']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kids_exams');
    }
};
