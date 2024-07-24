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
    Schema::create('students', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('random_number');
        $table->string('First_name');
        $table->string('Last_name');
        $table->string('Subject');
        $table->string('Level');
        $table->string('Email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('Password');
//        $table->date('Date_of_birthday');
        $table->enum('status', ['Active', 'InActive', 'Pending']);
        $table->string('Phone_number');
        $table->string('Home_number');
        $table->timestamps();
    });

    DB::statement("ALTER TABLE students ADD full_name VARCHAR(255) AS (CONCAT(First_name, ' ', Last_name))");
    DB::statement("ALTER TABLE students ADD CONCAT_NAME_RANDOM VARCHAR(255) AS (CONCAT(First_name, '_', random_number))");

    DB::unprepared('
        CREATE TRIGGER students_generate_random_number BEFORE INSERT ON students
        FOR EACH ROW SET NEW.random_number = FLOOR(RAND() * 900000) + 100000;
    ');
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
