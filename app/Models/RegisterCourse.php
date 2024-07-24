<?php

namespace App\Models;

use App\Enums\RegisterCourseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Notifications\Notifiable;

class RegisterCourse extends Model
{
    use HasFactory;
    public $table = "register_courses";
    protected $fillable =[
        'course_id',
        'student_id',
        'status',
        'Note'
    ];
    protected $casts = [
        'status' => RegisterCourseStatus::class,
    ];
    public function Course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id')
            ->join('series', 'series.id', '=', 'courses.serie_id')
            ->join('subjects', 'subjects.id', '=', 'series.subject_id')
            ->join('levels', 'levels.id', '=', 'series.level_id')
            ->selectRaw("courses.id, CONCAT(subjects.Language, ' ',subjects.Type,' ',levels.Number_latter) AS Course,Starting_Date")
            ->orderBy('courses.id');

    }
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id')
            ->selectRaw("id, CONCAT_NAME_RANDOM AS Berlitz_NAME")
            ->orderBy('id');
    }



}
