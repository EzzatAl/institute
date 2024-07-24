<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom_Schedules extends Model
{
    use HasFactory;
    public $table ="classrooms_schedules";
    protected $fillable =[
        'classroom_id',
        'schedule_id',
        'Month',
        'Day',
        'available'
    ];
    public function class_room (): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Classroom::class,'classroom_id');
    }
    public function schedule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id')
            ->selectRaw("id, schedule_name, CONCAT(schedule_name,'  ',Starting_time_with_AM_PM, ' To ', Ending_time_with_AM_PM) AS full_schedule")
            ->orderBy('id');
    }
}
