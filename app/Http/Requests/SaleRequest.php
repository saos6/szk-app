<?php

namespace App\Http\Requests;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Warehouse;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'    => ['required', 'exists:customers,id'],
            'employee_id'    => ['nullable', 'exists:employees,id'],
            'sale_date'      => ['required', 'date'],
            'order_date'     => ['nullable', 'date'],
            'delivery_date'  => ['nullable', 'date', 'after_or_equal:sale_date'],
            'partner_slip_no' => ['nullable', 'string', 'max:50'],
            'subject'        => ['required', 'string', 'max:200'],
            'status'         => ['required', Rule::in(array_keys(Sale::STATUSES))],
            'sale_type'      => ['nullable', Rule::in(array_keys(Sale::SALE_TYPES))],
            'remarks'        => ['nullable', 'string'],

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
            'items.*.selling_price'    => ['required', 'numeric', 'min:0'],
            'items.*.terminal_price'   => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate'         => ['required', Rule::in(['0', '10'])],
            'items.*.remarks'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required'           => '得意先は必須です。',
            'customer_id.exists'             => '得意先が存在しません。',
            'employee_id.exists'             => '担当者が存在しません。',
            'sale_date.required'             => '売上日は必須です。',
            'sale_date.date'                 => '売上日の形式が正しくありません。',
            'delivery_date.date'             => '納品日の形式が正しくありません。',
            'delivery_date.after_or_equal'   => '納品日は売上日以降の日付を入力してください。',
            'subject.required'               => '件名は必須です。',
            'subject.max'                    => '件名は200文字以内で入力してください。',
            'status.required'                => 'ステータスは必須です。',
            'status.in'                      => 'ステータスが正しくありません。',
            'items.required'                 => '明細を1行以上入力してください。',
            'items.min'                      => '明細を1行以上入力してください。',
            'items.*.model_code.required'    => '機種コード（商品）は必須です。',
            'items.*.model_code.max'         => '機種コード（商品）は20文字以内で入力してください。',
            'items.*.frame_number.required'  => 'フレームNo（品番）は必須です。',
            'items.*.frame_number.max'       => 'フレームNo（品番）は30文字以内で入力してください。',
            'items.*.quantity.required'      => '数量は必須です。',
            'items.*.quantity.min'           => '数量は0より大きい値を入力してください。',
            'items.*.purchase_price.required' => '仕入単価は必須です。',
            'items.*.purchase_price.min'     => '仕入単価は0以上の値を入力してください。',
            'items.*.selling_price.required' => '売上単価は必須です。',
            'items.*.selling_price.min'      => '売上単価は0以上の値を入力してください。',
            'items.*.tax_rate.required'      => '税率は必須です。',
            'items.*.tax_rate.in'            => '税率が正しくありません。',
        ];
    }
}
