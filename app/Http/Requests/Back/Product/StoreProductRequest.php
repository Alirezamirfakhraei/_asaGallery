<?php

namespace App\Http\Requests\Back\Product;

use App\Rules\CheckJdate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title'            => 'required|string|max:191',
            'title_en'         => 'nullable|string|max:191',
            'category_id'      => 'required|exists:categories,id',
            'image'            => 'image',
            'slug'             => "nullable|unique:products,slug",
            'publish_date'     => ['nullable', new CheckJdate('Y-m-d H:i:s')],
            'special_end_date' => ['nullable', new CheckJdate('Y-m-d H:i:s')],
            'spec_type'        => 'required_with:specification_group',
            'categories'       => 'nullable|array',
            'categories.*'     => 'exists:categories,id',
            'type'             => 'required|in:physical,download',
            'discount_expire'  => 'nullable',
            'rounding_amount'  => 'required|in:default,no,100,1000,10000,100000',
            'rounding_type'    => 'required|in:default,close,up,down',
            'currency_id'      => 'nullable|exists:currencies,id'
        ];

        if ($this->input('type') == 'physical') {
            $rules = array_merge($rules, [
                'weight'                      => 'required|integer',
                'unit'                        => 'required|string',
                'prices'                      => 'required_if:type,physical|array',
                'prices.*.productCode'        => 'required|string|unique:prices,productCode',
                'prices.*.price'              => 'nullable|numeric|min:0',
                'prices.*.stock'              => 'nullable|integer',
                'prices.*.attributes'         => "nullable|array",
                'prices.*.attributes.*'       => "nullable|exists:attributes,id",
                'prices.*.cart_max'           => 'nullable|integer',
                'prices.*.discount'           => 'nullable|min:0|max:100',
                'prices.*.discount_expire_at' => ['nullable', new CheckJdate('Y-m-d H:i:s')],
            ]);
        }

        if ($this->input('type') == 'download') {
            $rules = array_merge($rules, [
                'download_files'            => 'required_if:type,download|array',
                'download_files.*.title'    => 'required|string',
                'download_files.*.file'     => 'required_without:download_files.*.price_id|file',
                'download_files.*.price'    => 'required|numeric|min:0',
                'download_files.*.discount' => 'nullable|min:0|max:100',
                'download_files.*.status'   => 'required|in:active,inactive',
                'download_files.*.price_id' => 'nullable'
            ]);
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'special' => $this->has('special'),
        ]);
    }
}
