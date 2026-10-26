<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PropertyService
{
    public function paginateForOffice(
        int $officeId,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Property::withoutGlobalScopes()
            ->where('office_id', $officeId)
            ->with(['governorate', 'images'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function cursorPaginateAvailable(
        int $perPage = 15,
        array $filters = []
    ): CursorPaginator {
        $cache = app(PropertyCacheService::class);

        // ترتيب الفلاتر لتثبيت مفتاح الكاش.
        $cacheFilters = [
            'governorate_id' => $filters['governorate_id'] ?? null,
            'listing_type' => $filters['listing_type'] ?? null,
            'min_price' => $filters['min_price'] ?? null,
            'max_price' => $filters['max_price'] ?? null,
            'cursor' => $filters['cursor'] ?? null,
            'per_page' => $perPage,
        ];

        $cacheKey = 'properties:list:' . md5(
            json_encode($cacheFilters)
        );

        return $cache->rememberPropertyList(
            $cacheKey,
            fn () => Property::query()
                ->with(['governorate', 'images'])
                ->when(
                    isset($filters['governorate_id']),
                    fn (Builder $query) => $query->inGovernorate(
                        (int) $filters['governorate_id']
                    )
                )
                ->when(
                    isset($filters['listing_type']),
                    fn (Builder $query) => $query->ofListingType(
                        $filters['listing_type']
                    )
                )
                ->when(
                    isset($filters['min_price'])
                        || isset($filters['max_price']),
                    fn (Builder $query) => $query->withinPriceRange(
                        isset($filters['min_price'])
                            ? (float) $filters['min_price']
                            : null,
                        isset($filters['max_price'])
                            ? (float) $filters['max_price']
                            : null
                    )
                )
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->cursorPaginate($perPage)
        );
    }

    public function findAvailableProperty(
        int $propertyId,
        PropertyCacheService $cache
    ): Property {
        return $cache->rememberProperty(
            $propertyId,
            fn () => Property::query()
                ->with(['governorate', 'images'])
                ->findOrFail($propertyId)
        );
    }

    public function updateProperty(
        Property $property,
        array $data,
        PropertyCacheService $cache
    ): Property {
        $property->update($data);

        $cache->forgetProperties([$property->id]);

        return $property->fresh(['governorate', 'images']);
    }

    public function deleteProperty(
        Property $property,
        PropertyCacheService $cache
    ): void {
        $propertyId = $property->id;

        $property->delete();

        $cache->forgetProperties([$propertyId]);
    }
}
