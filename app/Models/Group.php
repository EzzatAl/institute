<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    public $table = "groups";
    protected $fillable = [
        'course_id',
        'employee_id',
        'classroom_id',
        'Group_number',
        'Number_Of_Units',
        'Ending_Date',
        'counter'
    ];
    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class,'course_id')
            ->join('series', 'series.id', '=', 'courses.serie_id')
            ->join('subjects', 'subjects.id', '=', 'series.subject_id')
            ->join('levels', 'levels.id', '=', 'series.level_id')
            ->selectRaw("courses.id, CONCAT(subjects.Language, ' ',subjects.Type,' ',levels.Number_latter) AS Course")
            ->orderBy('courses.id');
    }
    public function group_student(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupStudent::class, 'group_id');
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function class_room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Classroom::class,'classroom_id');
    }
    public function session(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Session::class,'group_id');
    }
}
