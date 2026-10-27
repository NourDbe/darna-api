<?php

namespace App\Http\Controllers\Api\V2;

use App\Contracts\PropertyOwnershipChecker;
use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyFilterRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\Api\V2\PropertyResource;
use App\Http\Resources\GovernorateStatisticsResource;
use App\Models\Property;
use App\Services\PropertyCacheService;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyService $propertyService
    ) {
    }

    public function index(
        PropertyFilterRequest $request
    ): AnonymousResourceCollection {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $properties = $this->propertyService
            ->cursorPaginateAvailable($perPage, $filters);

        return PropertyResource::collection($properties);
    }

    public function show(
        int $property,
        PropertyCacheService $cache
    ): PropertyResource {
        $propertyModel = $this->propertyService
            ->findAvailableProperty($property, $cache);

        return new PropertyResource($propertyModel);
    }

    public function update(
        UpdatePropertyRequest $request,
        int $property,
        PropertyCacheService $cache
    ): PropertyResource {
        $propertyModel = Property::withoutGlobalScopes()
            ->findOrFail($property);

        $updatedProperty = $this->propertyService->updateProperty(
            $propertyModel,
            $request->validated(),
            $cache
        );

        return new PropertyResource($updatedProperty);
    }

    public function destroy(
        Request $request,
        int $property,
        PropertyCacheService $cache
    ): JsonResponse {
        $user = $request->user();

        abort_unless($user, 401);

        $propertyModel = Property::withoutGlobalScopes()
            ->findOrFail($property);

        abort_unless(
            app(PropertyOwnershipChecker::class)
                ->owns($user, $propertyModel),
            403
        );

        $this->propertyService->deleteProperty(
            $propertyModel,
            $cache
        );

        return response()->json([
            'message' => 'تم حذف العقار بنجاح.',
        ]);
    }

    public function featured(
        PropertyCacheService $cache
    ): AnonymousResourceCollection {
        $properties = $this->propertyService
            ->getFeaturedProperties($cache);

        return PropertyResource::collection($properties);
    }

    public function governorateStatistics(
        PropertyCacheService $cache
    ): GovernorateStatisticsResource {
        $statistics = $this->propertyService
            ->getAvailableCountsByGovernorate($cache);

        return new GovernorateStatisticsResource([
            'counts' => $statistics,
        ]);
    }
}
