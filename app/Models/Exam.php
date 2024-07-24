<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;
    public $table ="exams";
    protected $fillable =[
        'group_student_id',
        'exam_type',
        'Written_Test',
        'Oral_Test',
        'Attendance',
        'Participation',
        'Home_Work',
        'Communication',
        'Vocabulary',
        'Structure',
        'Mark'
    ];
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupStudent::class, 'group_student_id')
            ->join('students', 'students.id', '=', 'group_students.student_id')
            ->selectRaw("group_students.id, students.CONCAT_NAME_RANDOM AS Berlitz_NAME")
            ->orderBy('group_students.id');
    }
    
}
