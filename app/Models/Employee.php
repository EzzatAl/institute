<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\BSON\Document;

class Employee extends Model
{
    use HasFactory,HasApiTokens,Notifiable;
    public $table = "employees";
    protected $fillable = [
        'First_name',
        'Last_name',
        'Full_name',
        'image_profile',
        'Nationality',
        'Language',
        'Email',
        'Password',
        'Phone_number',
        'Home_number',
        'Address',
        'email_verified_at'
    ];
protected static function booted()
{
 self::deleting(function (Employee $employee){
     Storage::delete("public/" .$employee->image_profile);
 });
}

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'Password' => 'hashed',
    ];
    public function employee_schedule(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee_Schedule::class,'employee_id')->where('Position','=','Teacher');
    }
    public function placement_tests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlacementTest::class,'employee_id');
    }
    public function group(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class,'employee_id');
    }
    public function session()
    {
        return $this->belongsTo(Session::class,'employee_id');
    }

}
