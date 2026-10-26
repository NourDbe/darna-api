<?php

namespace Tests\Feature;

use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function createPropertyFor(User $user): Property
    {
        $office = Office::create([
            'user_id' => $user->id,
            'name' => 'مكتب اختبار',
            'phone' => '0991234567',
            'address' => 'اللاذقية',
        ]);

        $owner = Owner::create([
            'name' => 'مالك اختبار',
        ]);

        $governorate = Governorate::create([
            'name' => 'محافظة اختبار',
        ]);

        return Property::withoutGlobalScopes()->create([
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'title' => 'عقار اختبار',
            'type' => 'apartment',
            'listing_type' => 'sale',
            'status' => 'available',
            'price' => 50000,
            'currency' => 'USD',
            'is_featured' => false,
            'approved_at' => now(),
        ]);
    }

    public function test_office_can_update_its_own_property(): void
    {
        $user = User::factory()->create();
        $property = $this->createPropertyFor($user);

        Sanctum::actingAs($user);

        $response = $this->patchJson(
            "/api/v1/properties/{$property->id}",
            ['title' => 'عنوان جديد']
        );

        $response->assertOk()
            ->assertJsonPath('data.title', 'عنوان جديد');
    }

    public function test_office_cannot_update_another_offices_property(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $property = $this->createPropertyFor($otherUser);

        Sanctum::actingAs($user);

        $this->patchJson(
            "/api/v1/properties/{$property->id}",
            ['title' => 'محاولة تعديل']
        )->assertForbidden();
    }

    public function test_office_cannot_delete_another_offices_property(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $property = $this->createPropertyFor($otherUser);

        Sanctum::actingAs($user);

        $this->deleteJson(
            "/api/v1/properties/{$property->id}"
        )->assertForbidden();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_guest_cannot_update_or_delete_properties(): void
    {
        $user = User::factory()->create();
        $property = $this->createPropertyFor($user);

        $this->patchJson(
            "/api/v1/properties/{$property->id}",
            ['title' => 'تعديل بدون تسجيل دخول']
        )->assertUnauthorized();

        $this->deleteJson(
            "/api/v1/properties/{$property->id}"
        )->assertUnauthorized();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
        ]);
    }
}
