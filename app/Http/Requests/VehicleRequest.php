<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function rules(): array
    {
        $vehicle = $this->route('vehicle');

        return [
            'model_code' => [
                'required', 'string', 'max:8',
                Rule::unique('vehicles')->where(function ($query) {
                    return $query->where('frame_number', $this->frame_number)->where('is_deleted', false);
                })->ignore($vehicle?->id),
            ],
            'frame_number' => 'required|string|max:10',
            'name1' => 'nullable|string|max:1000',
            'name2' => 'nullable|string|max:1000',
            'model_name' => 'nullable|string|max:1000',
            'model_type' => 'nullable|string|max:100',
            'model_number' => 'nullable|string|max:20',
            'color_code' => 'nullable|string|max:6',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'terminal_price' => 'nullable|numeric|min:0',
            'standard_retail_price' => 'nullable|numeric|min:0',
            'maker_code' => 'nullable|string|max:32',
            'unit' => 'nullable|string|max:10',
            'note1' => 'nullable|string|max:1000',
            'note2' => 'nullable|string|max:1000',
            'note3' => 'nullable|string|max:1000',
            'first_reg_date' => 'nullable|date',
            'second_reg_date' => 'nullable|date',
            'vehicle_no' => 'nullable|string|max:100',
            'owner_name' => 'nullable|string|max:200',
            'owner_kana' => 'nullable|string|max:200',
            'birth_date' => 'nullable|date',
            'zip_code' => 'nullable|string|max:10',
            'gender' => ['nullable', Rule::in(array_keys(Vehicle::GENDERS))],
            'address1' => 'nullable|string|max:200',
            'address2' => 'nullable|string|max:200',
            'tel' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'has_security_reg' => 'boolean',
            'security_reg_date' => 'nullable|date',
            'has_theft_insurance' => 'boolean',
            'theft_insurance_date' => 'nullable|date',
            'has_warranty' => 'boolean',
            'has_application' => 'boolean',
            'has_dm' => 'boolean',
            'remarks' => 'nullable|string|max:1000',
            'shop_name' => 'nullable|string|max:1000',
            'sale_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'model_code.required' => '機種コードは必須です。',
            'model_code.max' => '機種コードは8文字以内で入力してください。',
            'model_code.unique' => 'この機種コード・フレームNoの組み合わせはすでに使用されています。',
            'frame_number.required' => 'フレームNoは必須です。',
            'frame_number.max' => 'フレームNoは10文字以内で入力してください。',
            'purchase_price.numeric' => '仕入単価は数値で入力してください。',
            'selling_price.numeric' => '売上単価は数値で入力してください。',
            'gender.in' => '性別の値が不正です。',
            'birth_date.date' => '生年月日の日付形式が不正です。',
            'first_reg_date.date' => '初年度登録日の日付形式が不正です。',
            'second_reg_date.date' => '2回目登録日の日付形式が不正です。',
            'sale_date.date' => '売上日の日付形式が不正です。',
        ];
    }
}
