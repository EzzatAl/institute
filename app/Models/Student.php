<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Enums\StudentStatus;
use Doctrine\DBAL\Schema\Index;

class Student extends Model
{
    use HasFactory;

    use HasFactory;
    public $table = "students";

    protected $fillable = [
        'serie_id',
        'First_name',
        'random_number',
        'Last_name',
        'Subject',
        'Level',
        'Email',
        'Password',
        //'Date_of_birthday',
        'status',
        'Phone_number',
        'Home_number',
        'state'
        ];
    protected $hidden = [
        'Password',
        'remember_token',
    ];
    protected $casts = [
            'status' => StudentStatus::class,
            'email_verified_at' => 'datetime',
            'Password' => 'hashed',
            'Subject'=>'array',
            'Level'=>'array'
    ];
        public function getFullNameAttribute(): string
        {
            return $this->First_name . ' ' . $this->Last_name;
        }
        public function serie (): \Illuminate\Database\Eloquent\Relations\HasMany
        {
            return $this->hasMany(Serie::class,'serie_id');
        }
    public function group_student(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(GroupStudent::class, 'student_id')
            ->selectRaw("id, CONCAT_NAME_RANDOM AS Berlitz_NAME")
            ->orderBy('id');
    }
    public function Register_Course(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(RegisterCourse::class,'register_courses','student_id')
            ->selectRaw("id, CONCAT_NAME_RANDOM AS Berlitz_NAME")
            ->orderBy('id');
    }
    public function active(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupStudent::class, 'student_id')
            ->join('groups','groups.id','=','group_students.group_id')
            ->where('groups.Ending_Date','>=',Carbon::now()->format('Y-m-d'));

    }
    public function finished(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupStudent::class, 'student_id')
            ->join('groups','groups.id','=','group_students.group_id')
            ->where('groups.Ending_Date','<',Carbon::now()->format('Y-m-d'));

    }

        public function placement_test(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlacementTest::class,'Email','Email');
    }
    public function pending(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RegisterCourse::class,'student_id');
    }

    public function addSubject($subject): void
    {
        $subjects = $this->Subject ?? [];
        $subjects[] = $subject;
        $this->Subject = $subjects;
        $this->save();
    }

    public function addLevel($level): void
    {
        $levels = $this->Level ?? [];
        $levels[] = $level;
        $this->Level = $levels;
        $this->save();
    }
    public function changeLevel($level, $ind): void
    {
    $levels = $this->Level ?? [];
    if (isset($levels[$ind])) {
        $levels[$ind] = $level;
    } 
    $this->Level = $levels;
    $this->save();
}

}
