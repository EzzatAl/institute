<?php

namespace App\Models;

use App\Enums\CourseStatus;
use App\Enums\CourseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    use HasFactory;
    public $table = "courses";
    protected $fillable = [
        'serie_id', 
        'status',
        'Announcing', 
        'course_status', 
        'Day',
        'image',
        'Starting_Date',
        'course_time',
        'Lock_course'
    ];
    protected $casts = [
        'status' => CourseStatus::class,
        'course-status' => CourseType::class,
        'Day' => 'array',
        'course_time'=>'array'
    ];
    // In App\Models\Course.php
    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Course $course) {
            $course->registerCourses()->delete();
            Storage::delete("public/" . $course->image);
        });
    }
    public function serie(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Serie::class,'serie_id')
        ->selectRaw("series.id, CONCAT(subjects.Language, ' ',subjects.Type,' ',levels.Number_latter) AS series")
        ->join('subjects','subjects.id','=','series.subject_id')
        ->join('levels','levels.id','=','series.level_id')
        ->orderBy('series.id');
    }
    public function registerCourses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RegisterCourse::class, 'course_id');
    }
    public function course_session (): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Classroom_Schedules::class, 'course_id');
    }
    public function sessions()
{
    return $this->hasMany(Session::class);
}

}
