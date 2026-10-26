<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PropertyCacheService
{
    private const TTL = 300;

    private const PROPERTY_TAG = 'properties';
    private const FEATURED_TAG = 'properties:featured';
    private const GOVERNORATE_COUNTS_TAG = 'properties:governorate-counts';

    public function rememberProperty(int $propertyId, callable $callback): mixed
    {
        return Cache::store('redis')
            ->tags([self::PROPERTY_TAG])
            ->remember("properties:detail:{$propertyId}", self::TTL, $callback);
    }

    public function forgetProperty(int $propertyId): void
    {
        Cache::store('redis')
            ->tags([self::PROPERTY_TAG])
            ->forget("properties:detail:{$propertyId}");
    }

    public function rememberFeatured(callable $callback): mixed
    {
        return Cache::store('redis')
            ->tags([self::FEATURED_TAG])
            ->remember('properties:featured', self::TTL, $callback);
    }

    public function rememberGovernorateCounts(callable $callback): mixed
    {
        return Cache::store('redis')
            ->tags([self::GOVERNORATE_COUNTS_TAG])
            ->remember('properties:governorate-counts', self::TTL, $callback);
    }

    public function forgetPropertyLists(): void
    {
        Cache::store('redis')->tags([self::PROPERTY_TAG])->flush();
        Cache::store('redis')->tags([self::FEATURED_TAG])->flush();
        Cache::store('redis')->tags([self::GOVERNORATE_COUNTS_TAG])->flush();
    }

    public function forgetProperties(array $propertyIds): void
    {
        foreach (array_unique($propertyIds) as $propertyId) {
            $this->forgetProperty((int) $propertyId);
        }

        $this->forgetPropertyLists();
    }
    public function rememberPropertyList(string $key, callable $callback): mixed
{
    return Cache::store('redis')
        ->tags([self::PROPERTY_TAG])
        ->remember($key, self::TTL, $callback);
}
}
