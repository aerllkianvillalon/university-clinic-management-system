<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    public const PATIENT = 'patient';
    public const NURSE = 'nurse';
    public const DOCTOR = 'doctor';
    public const HEAD_NURSE = 'head_nurse';

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'email_verified_at'];

    protected $hidden = ['password', 'remember_token'];

    protected $attributes = ['role' => self::PATIENT, 'is_active' => true];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Every self-registered patient automatically gets a patient profile row.
        static::created(function (User $user) {
            if ($user->role === self::PATIENT) {
                $user->patient()->create([]);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'email', 'role', 'is_active'])
            ->logOnlyDirty()->dontLogEmptyChanges()->useLogName('user');
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function availability(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class, 'doctor_id');
    }

    public function clinicNotifications(): HasMany
    {
        return $this->hasMany(ClinicNotification::class);
    }
}
