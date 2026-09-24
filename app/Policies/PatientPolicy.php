<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::NURSE, User::DOCTOR, User::HEAD_NURSE);
    }

    public function view(User $user, Patient $patient): bool
    {
        return $this->viewAny($user) || $patient->user_id === $user->id;
    }
}
