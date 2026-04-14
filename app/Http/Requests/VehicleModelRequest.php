<?php

namespace App\Http\Requests;

use App\Models\VehicleModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleModelRequest extends FormRequest
{
    public function rules(): array
    {
        $vehicleModel = $this->route('vehicle_model');

        return [
            'model_code' => [
                'required', 'string', 'max:8',
                Rule::unique('vehicle_models')->where(function ($query) {
                    return $query->where('color_code', $this->color_code)->where('is_deleted', false);
                })->ignore($vehicleModel?->id),
            ],
            'color_code' => 'required|string|max:6',
            'model_name' => 'nullable|string|max:20',
            'model_abbr' => 'nullable|string|max:18',
            'base_model' => 'nullable|string|max:10',
            'model_name_kanji' => 'nullable|string|max:32',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'terminal_price' => 'nullable|numeric|min:0',
            'standard_retail_price' => 'nullable|numeric|min:0',
            'g1' => ['nullable', Rule::in(array_keys(VehicleModel::G1_TYPES))],
            'g2' => ['nullable', Rule::in(array_keys(VehicleModel::G2_DISP))],
            'g3' => ['nullable', Rule::in(array_keys(VehicleModel::G3_OPTIONS))],
            'g4' => ['nullable', Rule::in(array_keys(VehicleModel::G4_OPTIONS))],
            'g5' => ['nullable', Rule::in(array_keys(VehicleModel::G5_OPTIONS))],
            'order_number' => 'nullable|string|max:8',
            'tax_type' => ['nullable', Rule::in(array_keys(VehicleModel::ZEI_KBN))],
        ];
    }

    public function messages(): array
    {
        return [
            'model_code.required' => '機種コードは必須です。',
            'model_code.max' => '機種コードは8文字以内で入力してください。',
            'model_code.unique' => 'この機種コード・色コードの組み合わせはすでに使用されています。',
            'color_code.required' => '色コードは必須です。',
            'color_code.max' => '色コードは6文字以内で入力してください。',
            'model_name.max' => '営業機種記号は20文字以内で入力してください。',
            'model_abbr.max' => '機種略称は18文字以内で入力してください。',
            'base_model.max' => '基本機種は10文字以内で入力してください。',
            'model_name_kanji.max' => '機種名(漢字)は32文字以内で入力してください。',
            'purchase_price.numeric' => '仕入単価は数値で入力してください。',
            'purchase_price.min' => '仕入単価は0以上で入力してください。',
            'selling_price.numeric' => '売上単価は数値で入力してください。',
            'selling_price.min' => '売上単価は0以上で入力してください。',
            'g1.in' => 'タイプ区分の値が不正です。',
            'g2.in' => '排気量区分の値が不正です。',
            'g3.in' => 'G3の値が不正です。',
            'g4.in' => 'G4の値が不正です。',
            'g5.in' => 'G5の値が不正です。',
            'order_number.max' => 'オーダーNoは8文字以内で入力してください。',
            'tax_type.in' => '税区分の値が不正です。',
        ];
    }
}
