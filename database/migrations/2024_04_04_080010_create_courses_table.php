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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serie_id')->constrained('series')->onDelete('cascade');;
            $table->enum('status', ['To Open', 'Open','Finished']);
            $table->enum('course_status', ['Intensive', 'Regular','Private']);
            $table->string('Day');
            $table->string('image');
            $table->date('Starting_Date');
            $table->string('course_time');
            $table->boolean('Announcing')->default(false);
            $table->boolean('Lock_course')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
