<?php

namespace App\Services;

use App\Contracts\PropertyOwnershipChecker;
use App\Models\Property;
use App\Models\User;

class DefaultPropertyOwnershipChecker implements PropertyOwnershipChecker
{
    public function owns(User $user, Property $property): bool
    {
        return $property->office()
            ->where('user_id', $user->id)
            ->exists();
    }
}
