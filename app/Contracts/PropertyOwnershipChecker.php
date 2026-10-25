<?php

namespace App\Contracts;

use App\Models\Property;
use App\Models\User;

interface PropertyOwnershipChecker
{
    public function owns(User $user, Property $property): bool;
}
