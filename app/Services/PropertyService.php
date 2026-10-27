<?php

namespace App\Services;

use App\Models\Governorate;
use App\Models\Image;
use App\Models\Property;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Collection;

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
                ->cursorPaginate(
                    $perPage,
                    ['*'],
                    'cursor',
                    isset($filters['cursor'])
                        ? Cursor::fromEncoded($filters['cursor'])
                        : null
                )
        );
    }

    public function findAvailableProperty(
        int $propertyId,
        PropertyCacheService $cache
    ): Property {
        $data = $cache->rememberProperty(
            $propertyId,
            function () use ($propertyId): array {
                $property = Property::query()
                    ->with(['governorate', 'images'])
                    ->findOrFail($propertyId);

                return $this->serializeCachedProperty($property);
            }
        );

        return $this->hydrateCachedProperty($data);
    }

    public function updateProperty(
        Property $property,
        array $data,
        PropertyCacheService $cache
    ): Property {
        $oldGovernorateId = (int) $property->governorate_id;

        $property->update($data);
        $property->refresh();

        $newGovernorateId = (int) $property->governorate_id;

        $cache->forgetProperties([(int) $property->id]);
        $cache->forgetGovernorate($oldGovernorateId);

        if ($newGovernorateId !== $oldGovernorateId) {
            $cache->forgetGovernorate($newGovernorateId);
        }

        return $property->load(['governorate', 'images']);
    }

    public function deleteProperty(
        Property $property,
        PropertyCacheService $cache
    ): void {
        $propertyId = (int) $property->id;
        $governorateId = (int) $property->governorate_id;

        $property->delete();

        $cache->forgetProperties([$propertyId]);
        $cache->forgetGovernorate($governorateId);
    }

    public function getFeaturedProperties(
        PropertyCacheService $cache
    ): Collection {
        $data = $cache->rememberFeatured(
            fn () => Property::query()
                ->where('is_featured', true)
                ->with(['governorate', 'images'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->map(
                    fn (Property $property) =>
                        $this->serializeCachedProperty($property)
                )
                ->all()
        );

        return collect($data)->map(
            fn (array $item) => $this->hydrateCachedProperty($item)
        );
    }

    public function getAvailableCountsByGovernorate(
        PropertyCacheService $cache
    ): array {
        return $cache->rememberGovernorateCounts(
            fn () => Property::query()
                ->selectRaw(
                    'governorate_id, COUNT(*) as properties_count'
                )
                ->groupBy('governorate_id')
                ->get()
                ->mapWithKeys(
                    fn ($row) => [
                        'governorate_' . $row->governorate_id =>
                            (int) $row->properties_count,
                    ]
                )
                ->all()
        );
    }

    private function serializeCachedProperty(
        Property $property
    ): array {
        return [
            'attributes' => $property->getAttributes(),
            'governorate' => $property->governorate?->getAttributes(),
            'images' => $property->images
                ->map(fn (Image $image) => $image->getAttributes())
                ->all(),
        ];
    }

    private function hydrateCachedProperty(array $data): Property
    {
        $property = (new Property())->newFromBuilder(
            $data['attributes']
        );

        $governorate = $data['governorate'] !== null
            ? (new Governorate())->newFromBuilder(
                $data['governorate']
            )
            : null;

        $images = collect($data['images'])->map(
            fn (array $attributes) => (new Image())
                ->newFromBuilder($attributes)
        );

        return $property
            ->setRelation('governorate', $governorate)
            ->setRelation('images', $images);
    }
}
