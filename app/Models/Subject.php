<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory; 
    public $table = "subjects";
    protected $fillable = [
        'Language',
        'Type',
        'pre_condition',
        ]; 
        public function placementtests ()
        {
            return $this->hasMany(PlacementTest::class,'subject_id');
        }

} 
