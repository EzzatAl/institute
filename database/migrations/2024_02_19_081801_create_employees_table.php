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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('First_name');
            $table->string('Last_name');
            $table->string('Full_name');
            $table->string('Email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('Password');
            $table->string('image_profile');
            $table->string('Phone_number');
            $table->string('Home_number');
            $table->string('Nationality');
            $table->string('Language')->nullable();
            $table->string('Address');
            $table->rememberToken();
            $table->timestamps();
        });
        // DB::statement("ALTER TABLE employees ADD the_name VARCHAR(255) AS (CONCAT(First_name, ' ', Last_name))");

        DB::unprepared('
            CREATE TRIGGER employees_before_insert
            BEFORE INSERT ON employees
            FOR EACH ROW
            SET NEW.Full_name = CONCAT(NEW.First_name, " ", NEW.Last_name);
            ');

        DB::unprepared('
            CREATE TRIGGER employees_before_update
            BEFORE UPDATE ON employees
            FOR EACH ROW
            SET NEW.Full_name = CONCAT(NEW.First_name, " ", NEW.Last_name);
            ');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
