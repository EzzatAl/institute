<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('placement_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');;
            $table->foreignId('level_id')->nullable()->constrained('levels')->onDelete('cascade');;
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');;
            $table->string('First_name');
            $table->string('Last_name');
            $table->string('Email');
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('status', ['Canceled', 'Done', 'Not yet'])->default('Not yet');
            $table->string('Phone_number');
            $table->string('Home_number');
            $table->string('Notes')->nullable(true);
            $table->dateTime('Date_times')->nullable();
            $table->timestamps();
        });
    DB::statement("ALTER TABLE placement_tests ADD first_last_name VARCHAR(255) AS (CONCAT(First_name, ' ', Last_name))");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placement_tests');
    }
};
