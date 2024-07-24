<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;
    public $table = "assignments";

    protected $fillable = [
        'session_id',
        'homework',
        'homework_mark',
        ];
        public function session():  \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
        return $this->belongsTo(Session::class,'session_id')
                ->join('assignments','assignments.session_id','=','sessions.id')
                ->selectRaw("assignments.id,status")
                ->orderBy("assignments.id");
    }

}
 