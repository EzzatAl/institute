<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupStudent extends Model
{
    use HasFactory;
    public $table = "group_students";
    protected $fillable = [
        'group_id',
        'student_id',
        'Mark',
    ];
    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id')
            ->join('courses', 'courses.id', '=', 'groups.course_id')
            ->join('series', 'series.id', '=', 'courses.serie_id')
            ->join('subjects', 'subjects.id', '=', 'series.subject_id')
            ->join('levels', 'levels.id', '=', 'series.level_id')
            ->selectRaw("groups.id ,Group_number, CONCAT(subjects.Language, ' ',subjects.Type,' ',levels.Number_latter) AS Course,Ending_Date")
            ->orderBy('groups.id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id')
            ->selectRaw("id, CONCAT_NAME_RANDOM AS Berlitz_NAME")
            ->orderBy('id');
    }
    public function attendance(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class,'group_student_id');
    }
    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class,'group_student_id');
    }
    public function examkids(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KidsExam::class,'group_student_id');
    }
}
