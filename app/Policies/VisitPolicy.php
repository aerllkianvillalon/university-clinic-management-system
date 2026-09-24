<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function update(User $user, Visit $visit): bool
    {
        return ($user->hasRole(User::NURSE) && $visit->nurse_id === $user->id)
            || $user->hasRole(User::HEAD_NURSE);
    }
}
