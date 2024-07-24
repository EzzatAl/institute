<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlacementTest extends Model
{
    use HasFactory;

    public $table = "placement_tests";

    protected $fillable = [
        'level_id',
        'employee_id',
        'subject_id',
        'First_name',
        'Last_name',
        'Email',
        'status',
        'Phone_number',
        'Home_number',
        'Notes',
        'Date_times'
        ];

        protected $casts = [
            'status' => OrderStatus::class,
            'email_verified_at' => 'datetime',
            'Date_times'=>'datetime'
        ];
        public function employee()
        {
            return $this->belongsTo(Employee::class, 'employee_id');
        }
        public function level ()
        {
            return $this->belongsTo(Level::class,'level_id')
                ->orderBy('levels.id');
        }

        public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
            return $this->belongsTo(Subject::class, 'subject_id')
                ->selectRaw("id, CONCAT(Language, ' ', Type) AS serie")
                ->orderBy('id');
        }
    public function getFormattedNameAttribute(): string
    {
        return 'Student Name: ' . $this->First_name . ' ' . $this->Last_name;
    }
    public function getFormattedEmployeeAttribute(): string
    {
        return 'Moderator Name: ' . ($this->employee ? $this->employee->Full_name : 'Unknown');
    }
    public function getFormattedCourseAttribute(): string
    {
        return 'Series : ' . ($this->subject ? $this->subject->serie : 'Unknown');
    }
    public function getFormattedLevelAttribute(): string
    {
        return 'Series : ' . ($this->level ? $this->level->Number_latter : 'Unknown');
    }
    public function getFormattedEmailAttribute(): string
    {
        return 'Email : ' . $this->Email;
    }
    public function getFormattedStatusAttribute(): string
    {
        switch ($this->status) {
            case OrderStatus::Canceled:
                return 'Status: ' . OrderStatus::Canceled->value;
            case OrderStatus::Not_yet:
                return 'Status: ' . OrderStatus::Not_yet->value;
            case OrderStatus::Done:
                return 'Status: ' . OrderStatus::Done->value;
            default:
                return 'Status: Unknown';
        }
    }
    public function getFormattedPhoneNumberAttribute(): string
    {
        return 'Phone number : ' . $this->Phone_number;
    }
    public function getFormattedHomeNumberAttribute(): string
    {
        return 'Home number : ' . $this->Home_number;
    }
    public function getFormattedNotesAttribute(): string
    {
        return 'Notes : ' . $this->Notes;
    }
    public function getFormattedDateAttribute(): string
    {
        return 'Date times: ' . $this->Date_times->format('Y-m-d H:i:s');
    }
}
