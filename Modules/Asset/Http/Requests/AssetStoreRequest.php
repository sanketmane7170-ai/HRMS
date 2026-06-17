<?php

namespace Modules\Asset\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'unique_id' => ['required', 'unique:assets,unique_id'],
            'model' => ['required'],
            'asset_type_id' => ['required', 'exists:asset_types,id'],
            'asset_manufacturer_id' => ['required', 'exists:asset_manufacturers,id'],
            'description' => ['nullable'],
            'purchase_date' => ['nullable']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return  [
            'unique_id.required' => 'Please enter a valid serial number',
            'unique_id.unique' => 'Please enter a unique serial number.',
            'asset_type_id.*' => 'Please select a valid asset type',
            'asset_manufacturer_id.*' => 'Please select a valid asset manufacturer',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
