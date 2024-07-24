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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');;
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');;
            $table->string('Day');
            $table->boolean('teacher_Attendance');
            $table->string('Reason')->nullable();
            $table->string('Notes')->nullable();
            $table->boolean('shifting');
            $table->string('material_covered')->nullable();
            $table->integer('Unit')->nullable();
            $table->string('homework')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
