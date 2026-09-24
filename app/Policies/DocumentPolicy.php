<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return ($user->hasRole(User::PATIENT) && $user->patient?->id === $document->patient_id)
            || $user->hasRole(User::NURSE, User::DOCTOR, User::HEAD_NURSE);
    }

    public function delete(User $user, Document $document): bool
    {
        return $document->uploaded_by === $user->id || $user->hasRole(User::HEAD_NURSE);
    }
}
