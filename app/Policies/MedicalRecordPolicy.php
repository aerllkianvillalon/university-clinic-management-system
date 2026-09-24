<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    public function view(User $user, MedicalRecord $record): bool
    {
        // Voided records are only visible to the head nurse and the authoring doctor.
        if ($record->trashed() && ! $user->hasRole(User::HEAD_NURSE) && $record->doctor_id !== $user->id) {
            return false;
        }

        return match ($user->role) {
            User::PATIENT => $user->patient && $record->patient_id === $user->patient->id,
            // A nurse only sees records of visits she handled herself.
            User::NURSE => $record->visit && $record->visit->nurse_id === $user->id,
            User::DOCTOR => $record->doctor_id === $user->id
                || Appointment::where('patient_id', $record->patient_id)->where('doctor_id', $user->id)->exists(),
            User::HEAD_NURSE => true,
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::DOCTOR);
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        return $user->hasRole(User::DOCTOR) && $record->doctor_id === $user->id && ! $record->trashed();
    }

    /** "delete" = void (soft delete). */
    public function delete(User $user, MedicalRecord $record): bool
    {
        return $user->hasRole(User::HEAD_NURSE)
            || ($user->hasRole(User::DOCTOR) && $record->doctor_id === $user->id);
    }

    public function restore(User $user, MedicalRecord $record): bool
    {
        return $user->hasRole(User::HEAD_NURSE);
    }
}
