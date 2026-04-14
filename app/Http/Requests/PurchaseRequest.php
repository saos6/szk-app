<?php

namespace App\Http\Requests;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id'              => ['required', 'exists:suppliers,id'],
            'employee_id'              => ['nullable', 'exists:employees,id'],
            'purchase_date'            => ['required', 'date'],
            'subject'                  => ['required', 'string', 'max:200'],
            'status'                   => ['required', Rule::in(array_keys(Purchase::STATUSES))],
            'remarks'                  => ['nullable', 'string'],

            'items'                    => ['required', 'array', 'min:1'],
            'items.*.vehicle_id'       => ['nullable', 'exists:vehicles,id'],
            'items.*.model_code'       => ['required', 'string', 'max:20'],
            'items.*.frame_number'     => ['required', 'string', 'max:30'],
            'items.*.warehouse_code'   => ['nullable', 'string', 'max:20', Rule::exists('warehouses', 'code')->where('is_deleted', 0)],
            'items.*.color_code'       => ['nullable', 'string', 'max:6'],
            'items.*.model_name'       => ['nullable', 'string', 'max:200'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'             => ['nullable', 'string', 'max:10'],
            'items.*.purchase_price'   => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'         => ['required', Rule::in(['0', '10'])],
            'items.*.remarks'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required'            => '仕入先は必須です。',
            'supplier_id.exists'              => '仕入先が存在しません。',
            'employee_id.exists'              => '担当者が存在しません。',
            'purchase_date.required'          => '仕入日は必須です。',
            'purchase_date.date'              => '仕入日の形式が正しくありません。',
            'subject.required'                => '件名は必須です。',
            'subject.max'                     => '件名は200文字以内で入力してください。',
            'status.required'                 => 'ステータスは必須です。',
            'status.in'                       => 'ステータスが正しくありません。',
            'items.required'                  => '明細を1行以上入力してください。',
            'items.min'                       => '明細を1行以上入力してください。',
            'items.*.model_code.required'     => '機種コード（商品）は必須です。',
            'items.*.model_code.max'          => '機種コード（商品）は20文字以内で入力してください。',
            'items.*.frame_number.required'   => 'フレームNo（品番）は必須です。',
            'items.*.frame_number.max'        => 'フレームNo（品番）は30文字以内で入力してください。',
            'items.*.quantity.required'       => '数量は必須です。',
            'items.*.quantity.min'            => '数量は0より大きい値を入力してください。',
            'items.*.purchase_price.required' => '仕入単価は必須です。',
            'items.*.purchase_price.min'      => '仕入単価は0以上の値を入力してください。',
            'items.*.tax_rate.required'       => '税率は必須です。',
            'items.*.tax_rate.in'             => '税率が正しくありません。',
        ];
    }
}
