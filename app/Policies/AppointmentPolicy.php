<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($appointment->status !== Appointment::SCHEDULED) {
            return false;
        }

        return ($user->hasRole(User::PATIENT) && $user->patient?->id === $appointment->patient_id)
            || ($user->hasRole(User::DOCTOR) && $appointment->doctor_id === $user->id)
            || $user->hasRole(User::HEAD_NURSE);
    }

    public function checkIn(User $user, Appointment $appointment): bool
    {
        return $user->hasRole(User::NURSE, User::HEAD_NURSE)
            && $appointment->status === Appointment::SCHEDULED;
    }
}
