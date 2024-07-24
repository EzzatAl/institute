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
        Schema::create('series', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');;
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');;
            $table->string('category');
            $table->boolean('Primary_Series');
            $table->string('starting_age')->nullable(true);
            $table->string('ending_age')->nullable(true);
            $table->timestamps();
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
