<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Patient extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id', 'student_id', 'date_of_birth', 'sex', 'contact_number',
        'address', 'medical_history', 'consent_at',
    ];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'consent_at' => 'datetime'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        // medical_history is intentionally not logged (data minimisation).
        return LogOptions::defaults()->logOnly(['student_id', 'contact_number', 'address', 'consent_at'])
            ->logOnlyDirty()->dontLogEmptyChanges()->useLogName('patient');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
