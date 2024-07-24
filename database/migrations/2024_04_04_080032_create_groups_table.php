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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');;
             $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');;
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');;
            $table->string('Group_number');
            $table->integer('Number_Of_Units');
            $table->date('Ending_Date');
            $table->integer('counter')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
