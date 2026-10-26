<?php

namespace App\Http\Requests;

use App\Contracts\PropertyOwnershipChecker;
use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $propertyId = $this->route('property');

        if ($propertyId instanceof Property) {
            $property = $propertyId;
        } else {
            $property = Property::withoutGlobalScopes()
                ->find($propertyId);
        }

        if (! $property) {
            return false;
        }

        return app(PropertyOwnershipChecker::class)
            ->owns($user, $property);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', 'required', Rule::enum(PropertyType::class)],
            'listing_type' => ['sometimes', 'required', Rule::enum(ListingType::class)],
            'status' => ['sometimes', 'required', Rule::enum(PropertyStatus::class)],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'is_featured' => ['sometimes', 'boolean'],
            'governorate_id' => ['sometimes', 'required', 'integer', 'exists:governorates,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان العقار مطلوب.',
            'title.string' => 'عنوان العقار يجب أن يكون نصاً.',
            'title.max' => 'عنوان العقار يجب ألا يتجاوز 255 حرفاً.',
            'description.string' => 'وصف العقار يجب أن يكون نصاً.',
            'type.required' => 'نوع العقار مطلوب.',
            'type.enum' => 'نوع العقار المحدد غير صالح.',
            'listing_type.required' => 'نوع العرض مطلوب.',
            'listing_type.enum' => 'نوع العرض يجب أن يكون بيعاً أو إيجاراً.',
            'status.required' => 'حالة العقار مطلوبة.',
            'status.enum' => 'حالة العقار المحددة غير صالحة.',
            'price.required' => 'السعر مطلوب.',
            'price.numeric' => 'السعر يجب أن يكون رقماً.',
            'price.min' => 'السعر يجب ألا يكون سالباً.',
            'currency.required' => 'العملة مطلوبة.',
            'currency.size' => 'رمز العملة يجب أن يتكون من 3 أحرف.',
            'governorate_id.required' => 'المحافظة مطلوبة.',
            'governorate_id.exists' => 'المحافظة المحددة غير موجودة.',
        ];
    }
}
