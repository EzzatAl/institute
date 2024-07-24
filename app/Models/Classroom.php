<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;
    public $table = "classrooms";
    protected $fillable = [
        'Class_Number',
        'Capacity',
        'Notes',
        'State',
        ];
    public function group(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class,'classroom_id');
    }
}
