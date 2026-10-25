<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovedPropertyScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_approved_available_properties_are_returned_by_default(): void
    {
        $user = User::factory()->create();

        $office = Office::create([
            'user_id' => $user->id,
            'name' => 'Test Office',
        ]);

        $owner = Owner::create([
            'name' => 'Test Owner',
        ]);

        $governorate = Governorate::create([
            'name' => 'Latakia',
        ]);

        $approvedProperty = Property::create([
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'title' => 'Approved Property',
            'type' => 'apartment',
            'listing_type' => 'sale',
            'status' => PropertyStatus::Available->value,
            'price' => 100000,
            'currency' => 'USD',
            'approved_at' => now(),
        ]);

        Property::create([
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'title' => 'Pending Property',
            'type' => 'apartment',
            'listing_type' => 'sale',
            'status' => 'available',
            'price' => 90000,
            'currency' => 'USD',
            'approved_at' => null,
        ]);

        Property::create([
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'title' => 'Reserved Property',
            'type' => 'apartment',
            'listing_type' => 'sale',
            'status' => 'reserved',
            'price' => 80000,
            'currency' => 'USD',
            'approved_at' => now(),
        ]);

        $this->assertSame(
            [$approvedProperty->id],
            Property::pluck('id')->all()
        );
    }
}
