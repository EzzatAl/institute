<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $startTime = Carbon::createFromTime(9, 0, 0);
        $endTime = Carbon::createFromTime(21, 0, 0);
        $interval = Carbon::createFromTime(0, 45, 0);
        $sessionCounter = 1;
        while ($startTime < $endTime) {
            $sessionName =  'S' . $sessionCounter  ;

            DB::table('schedules')->insert([
                'schedule_name' => $sessionName,
                'Starting_time' => $startTime->format('H:i:s'),
                'Ending_time' => $startTime->addMinutes(45)->format('H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sessionCounter++;
        }
    }
}
