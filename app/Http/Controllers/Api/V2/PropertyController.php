<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyFilterRequest;
use App\Http\Resources\Api\V2\PropertyResource;
use App\Services\PropertyService;
use App\Services\PropertyCacheService;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use App\Contracts\PropertyOwnershipChecker;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyService $propertyService
    ) {
    }

    public function index(PropertyFilterRequest $request)
    {
        $filters = $request->validated();

        $perPage = (int) ($filters['per_page'] ?? 15);

        $properties = $this->propertyService->cursorPaginateAvailable(
            $perPage,
            $filters
        );

        return PropertyResource::collection($properties);
    }
    public function show(
    int $property,
    \App\Services\PropertyCacheService $cache
) {
    $propertyModel = $this->propertyService->findAvailableProperty(
        $property,
        $cache
    );

    return new PropertyResource($propertyModel);
}
public function update(
    UpdatePropertyRequest $request,
    int $property,
    PropertyCacheService $cache
) {
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
) {
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
}
