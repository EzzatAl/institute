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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->integer('Number');
            $table->string('letter')->nullable();
            $table->string('Number_latter');
            $table->string('test_type')->nullable();
            $table->timestamps();
        });
//        DB::unprepared('
//            CREATE TRIGGER levels_before_insert
//            BEFORE INSERT ON levels
//            FOR EACH ROW
//            SET NEW.Number_latter = CONCAT(NEW.Number, " ", NEW.letter);
//            ');
//
//        DB::unprepared('
//            CREATE TRIGGER levels_before_update
//            BEFORE UPDATE ON levels
//            FOR EACH ROW
//            SET NEW.Number_latter = CONCAT(NEW.Number, " ", NEW.letter);
//            ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
