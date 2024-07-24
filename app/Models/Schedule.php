<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    public $table = "schedules";
    protected $fillable =[
        'schedule_name',
        'Starting_time_with_AM_PM',
        'Ending_time_with_AM_PM',
        'Starting_time',
        'Ending_time',
    ];
    public function employee_schedule(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee_Schedule::class,'schedule_id');
    }
    public function course_session(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClassroomSchedules::class,'schedule_id');
    }

}
