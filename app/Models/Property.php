<?php

namespace App\Models;

use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Scopes\ApprovedPropertyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory;
    protected $fillable = [
        'office_id',
        'owner_id',
        'governorate_id',
        'title',
        'description',
        'type',
        'listing_type',
        'status',
        'price',
        'currency',
        'is_featured',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => PropertyType::class,
            'listing_type' => ListingType::class,
            'status' => PropertyStatus::class,
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function scopeInGovernorate(
        Builder $query,
        int $governorateId
    ): Builder {
        return $query->where('governorate_id', $governorateId);
    }

    public function scopeOfListingType(
        Builder $query,
        ListingType|string $listingType
    ): Builder {
        return $query->where(
            'listing_type',
            $listingType instanceof ListingType
                ? $listingType->value
                : $listingType
        );
    }

    public function scopeWithinPriceRange(
        Builder $query,
        ?float $minPrice = null,
        ?float $maxPrice = null
    ): Builder {
        return $query
            ->when(
                $minPrice !== null,
                fn (Builder $query) => $query->where('price', '>=', $minPrice)
            )
            ->when(
                $maxPrice !== null,
                fn (Builder $query) => $query->where('price', '<=', $maxPrice)
            );
    }
    protected static function booted(): void
{
    static::addGlobalScope(new ApprovedPropertyScope());

    static::deleting(function (Property $property) {
        $property->images()->delete();
    });
}
}
