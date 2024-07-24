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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_student_id')->constrained('group_students')->onDelete('cascade');
            $table->enum('Participation',['Fair','Good','Very Good','Excellent']);
            $table->enum('Vocabulary',['Fair','Good','Very Good','Excellent']);
            $table->enum('Behaviour',['Fair','Good','Very Good','Excellent']);
            $table->enum('Forming_Q_S',['Fair','Good','Very Good','Excellent']);
            $table->text('Notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
