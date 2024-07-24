<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;
    public $table = 'media';
    protected $fillable = [
        'url',
        'VIN', 
        'title',
        'description',
        'image',
        'video'
    ];
    protected $casts = [
        'image' => 'array',
        'video' => 'array',
    ];
    protected static function booted(): void
    {
        self::deleting(function (Media $media) {
            if (is_array($media->image) && count($media->image) > 0) {
                foreach ($media->image as $image) {
                    Storage::delete("public/" . $image);
                }
            }
            if (is_array($media->video) && count($media->video) > 0) {
                foreach ($media->video as $video) {
                    Storage::delete("public/" . $video);
                }
            }
        });
    }

}
