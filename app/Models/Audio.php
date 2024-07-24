<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Audio extends Model
{
    use HasFactory;
    public $table = "audios";
    protected $fillable = [
        'serie_id',
        'audio_name',
        'audio_path',
    ];
    protected $casts = [
        'audio_path'=>'array',
        'audio_name'=>'array'
    ];
    protected static function booted()
    {
        self::deleting(function (Audio $audio) {
            if (is_array($audio->audio_path)) {
                        foreach ($audio->audio_name as $audio)
                        {
                            Storage::delete("public/audios/" . $audio);
                        }
                    }
            else
            {
                Storage::delete("public/audios/" . $audio->audio_name);
            }

        });
    }
        public function serie()
    {
        return $this->belongsTo(Serie::class, 'serie_id')
            ->join('levels', 'series.level_id', '=', 'levels.id')
            ->leftJoin('subjects', 'series.subject_id', '=', 'subjects.id')
            ->selectRaw("series.id, CONCAT(subjects.Language, ' ',subjects.Type,' ',levels.Number_latter) AS serie")
            ->orderBy('series.id');
    }
}
