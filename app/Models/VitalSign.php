<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class VitalSign extends Model
{
    use LogsActivity;

    protected $fillable = [
        'visit_id', 'temperature', 'bp_systolic', 'bp_diastolic',
        'heart_rate', 'respiratory_rate', 'height', 'weight',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()
            ->dontLogEmptyChanges()->useLogName('vital_signs');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /** Simple adult reference ranges used to flag readings for the nurse/doctor. */
    public function abnormalFlags(): array
    {
        $flags = [];
        if ($this->temperature >= 38 || $this->temperature < 35.5) $flags[] = 'Temperature';
        if ($this->bp_systolic >= 140 || $this->bp_systolic < 90 || $this->bp_diastolic >= 90 || $this->bp_diastolic < 60) $flags[] = 'Blood pressure';
        if ($this->heart_rate > 100 || $this->heart_rate < 50) $flags[] = 'Heart rate';
        if ($this->respiratory_rate > 20 || $this->respiratory_rate < 12) $flags[] = 'Respiratory rate';

        return $flags;
    }
}
