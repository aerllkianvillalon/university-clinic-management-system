<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\ClinicNotification;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'clinic:send-reminders';
    protected $description = 'Notify patients about their appointments scheduled for tomorrow';

    public function handle(): int
    {
        $count = 0;
        Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', today()->addDay())
            ->where('status', Appointment::SCHEDULED)
            ->each(function (Appointment $a) use (&$count) {
                ClinicNotification::send(
                    $a->patient->user_id,
                    'appointment_reminder',
                    'Appointment tomorrow',
                    "Reminder: you have an appointment with Dr. {$a->doctor->name} tomorrow at {$a->time_label}."
                );
                $count++;
            });

        $this->info("Sent {$count} reminder(s).");

        return self::SUCCESS;
    }
}
