<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    use HasFactory;
    public $table = "series";
    protected $fillable = [
        'subject_id',
        'level_id',
        'category',
        'Primary_Series',
        'starting_age',
        'ending_age',
    ];
    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
    return $this->belongsTo(Subject::class, 'subject_id')
        ->selectRaw("id, CONCAT(Language, ' ', Type) AS serie")
        ->orderBy('subjects.id');
    }
    public function scopeConcatenatedSearch(Builder $query, $search)
    {
        return $query->whereRaw("CONCAT(Language, ' ', Type) = ?", [$search]);
    }
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id')
            ->orderBy('levels.id');
    }
    public function audio (): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Audio::class,'serie_id');
    }
    public function student (): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Student::class,'serie_id');
    }
    public function course(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Course::class,'serie_id');
    }
}
