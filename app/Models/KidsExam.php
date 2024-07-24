<?php

namespace App\Models;

use App\Enums\Evaluationn;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KidsExam extends Model
{
    use HasFactory;
    public $table ="kids_exams";
    protected $fillable =[
        'group_student_id',
        'Communication', 
        'Vocabulary',
        'Structure',
    ];
    protected $casts = [
        'Communication' => Evaluationn::class,
        'Vocabulary' => Evaluationn::class,
        'Structure' => Evaluationn::class,
    ];
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupStudent::class, 'group_student_id')
            ->join('students', 'students.id', '=', 'group_students.student_id')
            ->selectRaw("group_students.id, students.CONCAT_NAME_RANDOM AS Berlitz_NAME")
            ->orderBy('group_students.id');
    }
}
 