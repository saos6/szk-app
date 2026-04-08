<?php

namespace App\Http\Requests;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'sale_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:sale_date'],
            'subject' => ['required', 'string', 'max:200'],
            'status' => ['required', Rule::in(array_keys(Sale::STATUSES))],
            'remarks' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'items.*.kisyu_cd' => ['nullable', 'string', 'max:8'],
            'items.*.frame_no' => ['nullable', 'string', 'max:30'],
            'items.*.iro_cd' => ['nullable', 'string', 'max:6'],
            'items.*.kisyu_nm' => ['nullable', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:10'],
            'items.*.sre_tan' => ['required', 'numeric', 'min:0'],
            'items.*.uri_tan' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', Rule::in(['0', '10'])],
            'items.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => '得意先は必須です。',
            'customer_id.exists' => '得意先が存在しません。',
            'employee_id.exists' => '担当者が存在しません。',
            'sale_date.required' => '売上日は必須です。',
            'sale_date.date' => '売上日の形式が正しくありません。',
            'delivery_date.date' => '納品日の形式が正しくありません。',
            'delivery_date.after_or_equal' => '納品日は売上日以降の日付を入力してください。',
            'subject.required' => '件名は必須です。',
            'subject.max' => '件名は200文字以内で入力してください。',
            'status.required' => 'ステータスは必須です。',
            'status.in' => 'ステータスが正しくありません。',
            'items.required' => '明細を1行以上入力してください。',
            'items.min' => '明細を1行以上入力してください。',
            'items.*.quantity.required' => '数量は必須です。',
            'items.*.quantity.min' => '数量は0より大きい値を入力してください。',
            'items.*.sre_tan.required' => '仕入単価は必須です。',
            'items.*.sre_tan.min' => '仕入単価は0以上の値を入力してください。',
            'items.*.uri_tan.required' => '売上単価は必須です。',
            'items.*.uri_tan.min' => '売上単価は0以上の値を入力してください。',
            'items.*.tax_rate.required' => '税率は必須です。',
            'items.*.tax_rate.in' => '税率が正しくありません。',
        ];
    }
}
