<?php

namespace Tests\Feature;

use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use App\Services\PropertyCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PropertyCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_property_invalidates_cached_property_details(): void
    {
        $user = User::factory()->create();

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

        $property = Property::withoutGlobalScopes()->create([
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'title' => 'عنوان قديم',
            'type' => 'apartment',
            'listing_type' => 'sale',
            'status' => 'available',
            'price' => 50000,
            'currency' => 'USD',
            'is_featured' => false,
            'approved_at' => now(),
        ]);

        $cache = app(PropertyCacheService::class);
        $cache->forgetProperties([$property->id]);

        $cache->rememberProperty(
            $property->id,
            fn () => ['title' => 'عنوان قديم']
        );

        $this->assertSame(
            ['title' => 'عنوان قديم'],
            $cache->rememberProperty(
                $property->id,
                fn () => ['title' => 'قيمة جديدة غير متوقعة']
            )
        );

        app(\App\Services\PropertyService::class)->updateProperty(
        $property,
        ['title' => 'عنوان جديد'],
        $cache
);
        $this->assertSame(
            ['title' => 'عنوان جديد'],
            $cache->rememberProperty(
                $property->id,
                fn () => ['title' => $property->fresh()->title]
            )
        );
    }
    public function test_deleting_property_invalidates_cached_property_details(): void
{
    $user = User::factory()->create();

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

    $property = Property::withoutGlobalScopes()->create([
        'office_id' => $office->id,
        'owner_id' => $owner->id,
        'governorate_id' => $governorate->id,
        'title' => 'عقار للحذف',
        'type' => 'apartment',
        'listing_type' => 'sale',
        'status' => 'available',
        'price' => 50000,
        'currency' => 'USD',
        'is_featured' => false,
        'approved_at' => now(),
    ]);

    $cache = app(PropertyCacheService::class);

    $cache->rememberProperty(
        $property->id,
        fn () => ['title' => 'عقار للحذف']
    );

    app(\App\Services\PropertyService::class)->deleteProperty(
        $property,
        $cache
    );

    $this->assertDatabaseMissing('properties', [
        'id' => $property->id,
    ]);

    $this->assertSame(
        ['title' => 'قيمة جديدة'],
        $cache->rememberProperty(
            $property->id,
            fn () => ['title' => 'قيمة جديدة']
        )
    );
}
public function test_property_list_cursor_pagination_does_not_repeat_items(): void
{
    $user = User::factory()->create();

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

    foreach (range(1, 3) as $number) {
        Property::withoutGlobalScopes()->create([
            'office_id' => $office->id,
            'owner_id' => $owner->id,
            'governorate_id' => $governorate->id,
            'title' => "عقار اختبار {$number}",
            'type' => 'apartment',
            'listing_type' => 'sale',
            'status' => 'available',
            'price' => 50000 + $number,
            'currency' => 'USD',
            'is_featured' => false,
            'approved_at' => now(),
        ]);
    }

    $cache = app(PropertyCacheService::class);
    $cache->forgetProperties([]);

    $service = app(\App\Services\PropertyService::class);

    $firstPage = $service->cursorPaginateAvailable(2);
    $firstIds = collect($firstPage->items())->pluck('id');

    $this->assertCount(2, $firstIds);

    $nextCursor = $firstPage->nextCursor();

    $this->assertNotNull($nextCursor);

    $secondPage = $service->cursorPaginateAvailable(2, [
        'cursor' => $nextCursor->encode(),
    ]);

    $secondIds = collect($secondPage->items())->pluck('id');


    $this->assertNotEmpty($secondIds);
    $this->assertSame(
        0,
        $firstIds->intersect($secondIds)->count()
    );
}
}
