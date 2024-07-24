<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    public $table = "sessions";

    protected $fillable = [
        'group_id',
        'employee_id',
        'Day',
        'teacher_Attendance',
        'Reason',
        'Notes',
        'shifting',
        'material_covered',
        'Unit'
        ];
        public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
            return $this->belongsTo(Group::class,'group_id');
        }
        public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
            return $this->belongsTo(Employee::class,'employee_id');
        }
        public function attendance(): \Illuminate\Database\Eloquent\Relations\HasMany
        {
            return $this->hasMany(Attendance::class,'session_id');
        }
        public function assignment(): \Illuminate\Database\Eloquent\Relations\HasMany
        {
            return $this->hasMany(Assignment::class,'session_id');
        }
        public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
        return $this->belongsTo(Course::class, 'course_id');
        }





}
