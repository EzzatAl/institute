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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_name');
            $table->string('Starting_time_with_AM_PM')->nullable('false');
            $table->string('Ending_time_with_AM_PM')->nullable('false');
            $table->timeTz('Starting_time');
            $table->timeTz('Ending_time');
            $table->timestamps();
        });

        // Create trigger for before insert
        DB::unprepared('
        CREATE TRIGGER schedules_before_insert
        BEFORE INSERT ON schedules
        FOR EACH ROW
        BEGIN
            DECLARE start_hour INT;
            DECLARE start_minute INT;
            DECLARE end_hour INT;
            DECLARE end_minute INT;

            SET start_hour = HOUR(NEW.Starting_time);
            SET start_minute = MINUTE(NEW.Starting_time);
            SET end_hour = HOUR(NEW.Ending_time);
            SET end_minute = MINUTE(NEW.Ending_time);

            IF start_hour BETWEEN 13 AND 23 THEN
                SET NEW.Starting_time_with_AM_PM = CONCAT(start_hour - 12, ":", LPAD(start_minute, 2, "0"), " PM");
            ELSEIF start_hour = 12 THEN
                SET NEW.Starting_time_with_AM_PM = CONCAT(start_hour , ":", LPAD(start_minute, 2, "0"), " PM");
            ELSE
                SET NEW.Starting_time_with_AM_PM = CONCAT(start_hour, ":", LPAD(start_minute, 2, "0"), " AM");
            END IF;

            IF end_hour BETWEEN 13 AND 23 THEN
                SET NEW.Ending_time_with_AM_PM = CONCAT(end_hour - 12, ":", LPAD(end_minute, 2, "0"), " PM");
            ELSEIF end_hour = 12 THEN
                SET NEW.Ending_time_with_AM_PM = CONCAT(end_hour, ":", LPAD(end_minute, 2, "0"), " PM");
            ELSE
                SET NEW.Ending_time_with_AM_PM = CONCAT(end_hour, ":", LPAD(end_minute, 2, "0"), " AM");
            END IF;
        END
    ');

        // Create trigger for before update
        DB::unprepared('
        CREATE TRIGGER schedules_before_update
        BEFORE UPDATE ON schedules
        FOR EACH ROW
        BEGIN
            DECLARE start_hour INT;
            DECLARE start_minute INT;
            DECLARE end_hour INT;
            DECLARE end_minute INT;

            SET start_hour = HOUR(NEW.Starting_time);
            SET start_minute = MINUTE(NEW.Starting_time);
            SET end_hour = HOUR(NEW.Ending_time);
            SET end_minute = MINUTE(NEW.Ending_time);

             IF start_hour BETWEEN 13 AND 23 THEN
                SET NEW.Starting_time_with_AM_PM = CONCAT(start_hour - 12, ":", LPAD(start_minute, 2, "0"), " PM");
            ELSEIF start_hour = 12 THEN
                SET NEW.Starting_time_with_AM_PM = CONCAT(start_hour , ":", LPAD(start_minute, 2, "0"), " PM");
            ELSE
                SET NEW.Starting_time_with_AM_PM = CONCAT(start_hour, ":", LPAD(start_minute, 2, "0"), " AM");
            END IF;

            IF end_hour BETWEEN 13 AND 23 THEN
                SET NEW.Ending_time_with_AM_PM = CONCAT(end_hour - 12, ":", LPAD(end_minute, 2, "0"), " PM");
            ELSEIF start_hour = 12 THEN
                SET NEW.Starting_time_with_AM_PM = CONCAT(start_hour , ":", LPAD(start_minute, 2, "0"), " PM");
            ELSE
                SET NEW.Ending_time_with_AM_PM = CONCAT(end_hour, ":", LPAD(end_minute, 2, "0"), " AM");
            END IF;
        END
    ');
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
