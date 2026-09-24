<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Appointment extends Model
{
    use LogsActivity;

    public const SCHEDULED = 'scheduled';
    public const CHECKED_IN = 'checked_in';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    protected $fillable = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'reason', 'status'];

    protected function casts(): array
    {
        return ['appointment_date' => 'date'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()
            ->dontLogEmptyChanges()->useLogName('appointment');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }

    public function getTimeLabelAttribute(): string
    {
        return Carbon::parse($this->appointment_time)->format('g:i A');
    }

    /**
     * Open "HH:MM" slots for a doctor on a date, built from doctor_availability
     * minus existing (non-cancelled) bookings and slots already in the past.
     */
    public static function availableSlots(int $doctorId, Carbon|string $date): array
    {
        $date = Carbon::parse($date)->startOfDay();

        $windows = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('day_of_week', $date->dayOfWeek)
            ->orderBy('start_time')->get();

        $taken = static::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', self::CANCELLED)
            ->pluck('appointment_time')
            ->map(fn ($t) => substr($t, 0, 5))->all();

        $slots = [];
        foreach ($windows as $w) {
            $cursor = $date->copy()->setTimeFromTimeString($w->start_time);
            $end = $date->copy()->setTimeFromTimeString($w->end_time);
            $step = max(5, (int) $w->slot_duration_minutes);

            while ($cursor->copy()->addMinutes($step)->lte($end)) {
                $label = $cursor->format('H:i');
                if ($cursor->isFuture() && ! in_array($label, $taken, true)) {
                    $slots[] = $label;
                }
                $cursor->addMinutes($step);
            }
        }

        return $slots;
    }
}
