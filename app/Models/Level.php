<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;
    public $table = "levels";
    protected $fillable = [
        'Number',
        'letter',
        'Number_latter',
        'uribe levels',
        'test_type',
        ];
    protected $casts = [
        'test_type' => 'array',
    ];
    public function serie()
    {
        $this->hasMany(Serie::class,'level_id');
    }
    public function placement_tests()
    {
        return $this->hasMany(PlacementTest::class,'level_id');
    }
}
