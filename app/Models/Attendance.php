<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public $table = "attendances";

    protected $fillable = [
        'session_id',
        'group_student_id',
        'status', 
        'Notes',
    ];
    public function session(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Session::class,'session_id')
            ->join('attendances','attendances.session_id','=','sessions.id')
            ->selectRaw("attendances.id,status")
            ->orderBy("attendances.id");
    }
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupStudent::class, 'group_student_id')
            ->join('students', 'students.id', '=', 'group_students.student_id')
            ->selectRaw("group_students.id, students.CONCAT_NAME_RANDOM AS Berlitz_NAME")
            ->orderBy('group_students.id');
    }
}


