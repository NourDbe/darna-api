<?php

namespace Tests\Feature;

use App\Contracts\PropertyOwnershipChecker;
use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyOwnershipCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_true_when_property_belongs_to_users_office(): void
    {
        $user = User::factory()->create();
        $office = Office::create([
            'user_id' => $user->id,
            'name' => 'مكتب تجريبي',
            'phone' => '0991234567',
            'address' => 'اللاذقية',
        ]);
        $owner = Owner::create(['name' => 'مالك تجريبي']);
        $governorate = Governorate::create(['name' => 'اللاذقية']);

        $property = Property::withoutGlobalScopes()->create([
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'title' => 'شقة للاختبار',
            'type' => 'apartment',
            'listing_type' => 'sale',
            'status' => 'available',
            'price' => 50000,
            'currency' => 'USD',
            'is_featured' => false,
            'approved_at' => now(),
        ]);

        $this->assertTrue(
            app(PropertyOwnershipChecker::class)->owns($user, $property)
        );
    }

    public function test_it_returns_false_when_property_belongs_to_another_office(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $office = Office::create([
        'user_id' => $otherUser->id,
        'name' => 'مكتب آخر',
        'phone' => '0997654321',
        'address' => 'دمشق',
    ]);
        $owner = Owner::create(['name' => 'مالك تجريبي']);
        $governorate = Governorate::create(['name' => 'دمشق']);

        $property = Property::withoutGlobalScopes()->create([
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'title' => 'عقار مكتب آخر',
            'type' => 'apartment',
            'listing_type' => 'sale',
            'status' => 'available',
            'price' => 60000,
            'currency' => 'USD',
            'is_featured' => false,
            'approved_at' => now(),
        ]);

        $this->assertFalse(
            app(PropertyOwnershipChecker::class)->owns($user, $property)
        );
    }
}
