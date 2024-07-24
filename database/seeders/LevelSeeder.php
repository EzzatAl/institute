<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

    class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i <= 6; $i++) {
            $levelA = new Level();
            $levelA->Number = $i;
            $levelA->letter = 'A';
            $levelA->Number_latter = $i . 'A';
            $levelA->test_type = 'Oral';
            $levelA->save();

            $levelB = new Level();
            $levelB->Number = $i;
            $levelB->letter = 'B';
            $levelB->Number_latter = $i . 'B';
            $levelB->test_type = 'Written';
            $levelB->save();

            $levelB = new Level();
            $levelB->Number = $i;
            $levelB->letter = 'C';
            $levelB->Number_latter = $i . 'C';
            $levelB->test_type =null;
            $levelB->save();
        }
        for ($i = 7; $i <= 8; $i++) {
            $levelA = new Level();
            $levelA->Number = $i;
            $levelA->letter = 'A';
            $levelA->Number_latter = $i . 'A';
            $levelA->test_type = 'Oral';
            $levelA->save();

            $levelB = new Level();
            $levelB->Number = $i;
            $levelB->letter = 'B';
            $levelB->Number_latter = $i . 'B';
            $levelB->test_type = 'Written';
            $levelB->save();
        }
        for ($i = 9; $i <= 10; $i++) {
            $level = new Level();
            $level->Number = $i;
            $level->letter = null;
            $level->Number_latter = $i;
            $level->test_type = ['Oral','Written'];
            $level->save();
        }
    }
}
