<?php

namespace App\Policies;

use App\Models\Generation;
use App\Models\User;

class GenerationPolicy
{
    /**
     * Determine if the user can view the generation.
     */
    public function view(User $user, Generation $generation): bool
    {
        return $user->id === $generation->user_id;
    }
}
