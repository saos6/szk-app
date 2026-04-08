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
            'kisyu_cd' => [
                'required', 'string', 'max:8',
                Rule::unique('vehicle_models')->where(function ($query) {
                    return $query->where('iro_cd', $this->iro_cd)->where('is_deleted', false);
                })->ignore($vehicleModel?->id),
            ],
            'iro_cd' => 'required|string|max:6',
            'kisyu_nm' => 'nullable|string|max:20',
            'kisyu_nm_r' => 'nullable|string|max:18',
            'kihon' => 'nullable|string|max:10',
            'kisyu_nm_h' => 'nullable|string|max:32',
            'sre_tan' => 'nullable|numeric|min:0',
            'uri_tan' => 'nullable|numeric|min:0',
            'g1' => ['nullable', Rule::in(array_keys(VehicleModel::G1_TYPES))],
            'g2' => ['nullable', Rule::in(array_keys(VehicleModel::G2_DISP))],
            'g3' => ['nullable', Rule::in(array_keys(VehicleModel::G3_OPTIONS))],
            'g4' => ['nullable', Rule::in(array_keys(VehicleModel::G4_OPTIONS))],
            'g5' => ['nullable', Rule::in(array_keys(VehicleModel::G5_OPTIONS))],
            'order_no' => 'nullable|string|max:8',
            'zei_kbn' => ['nullable', Rule::in(array_keys(VehicleModel::ZEI_KBN))],
        ];
    }

    public function messages(): array
    {
        return [
            'kisyu_cd.required' => '機種コードは必須です。',
            'kisyu_cd.max' => '機種コードは8文字以内で入力してください。',
            'kisyu_cd.unique' => 'この機種コード・色コードの組み合わせはすでに使用されています。',
            'iro_cd.required' => '色コードは必須です。',
            'iro_cd.max' => '色コードは6文字以内で入力してください。',
            'kisyu_nm.max' => '営業機種記号は20文字以内で入力してください。',
            'kisyu_nm_r.max' => '機種略称は18文字以内で入力してください。',
            'kihon.max' => '基本機種は10文字以内で入力してください。',
            'kisyu_nm_h.max' => '機種名(漢字)は32文字以内で入力してください。',
            'sre_tan.numeric' => '仕入単価は数値で入力してください。',
            'sre_tan.min' => '仕入単価は0以上で入力してください。',
            'uri_tan.numeric' => '売上単価は数値で入力してください。',
            'uri_tan.min' => '売上単価は0以上で入力してください。',
            'g1.in' => 'タイプ区分の値が不正です。',
            'g2.in' => '排気量区分の値が不正です。',
            'g3.in' => 'G3の値が不正です。',
            'g4.in' => 'G4の値が不正です。',
            'g5.in' => 'G5の値が不正です。',
            'order_no.max' => 'オーダーNoは8文字以内で入力してください。',
            'zei_kbn.in' => '税区分の値が不正です。',
        ];
    }
}
