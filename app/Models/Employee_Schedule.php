<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee_Schedule extends Model
{
    use HasFactory;
    public $table = "employee_schedules";
    protected $fillable =[
        'employee_id',
        'schedule_id',
        'Month',
        'Day',
        'available'
    ];
    public function employee (): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function schedule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id')
            ->selectRaw("id, schedule_name, CONCAT(schedule_name,'  ', Starting_time_with_AM_PM, ' To ', Ending_time_with_AM_PM) AS full_schedule")
            ->orderBy('id');
    }
}
