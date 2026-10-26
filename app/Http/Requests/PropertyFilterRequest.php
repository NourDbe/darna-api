<?php

namespace App\Http\Requests;

use App\Enums\ListingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'governorate_id' => ['sometimes', 'integer', 'exists:governorates,id'],
            'listing_type' => ['sometimes', Rule::enum(ListingType::class)],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => [
                'sometimes',
                'numeric',
                'min:0',
                'gte:min_price',
            ],
            'cursor' => ['sometimes', 'nullable', 'string'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'عدد العناصر في الصفحة يجب أن يكون عدداً صحيحاً.',
            'per_page.min' => 'يجب أن يحتوي حجم الصفحة على عنصر واحد على الأقل.',
            'per_page.max' => 'الحد الأقصى للعناصر في الصفحة هو 50.',
            'governorate_id.integer' => 'رقم المحافظة يجب أن يكون عدداً صحيحاً.',
            'governorate_id.exists' => 'المحافظة المحددة غير موجودة.',
            'listing_type.enum' => 'نوع العرض يجب أن يكون بيعاً أو إيجاراً.',
            'min_price.numeric' => 'الحد الأدنى للسعر يجب أن يكون رقماً.',
            'min_price.min' => 'الحد الأدنى للسعر لا يمكن أن يكون سالباً.',
            'max_price.numeric' => 'الحد الأعلى للسعر يجب أن يكون رقماً.',
            'max_price.min' => 'الحد الأعلى للسعر لا يمكن أن يكون سالباً.',
            'max_price.gte' => 'الحد الأعلى للسعر يجب أن يكون أكبر من أو يساوي الحد الأدنى.',
            'page.integer' => 'رقم الصفحة يجب أن يكون عدداً صحيحاً.',
            'page.min' => 'رقم الصفحة يجب أن يكون واحداً على الأقل.',
        ];
    }
}
