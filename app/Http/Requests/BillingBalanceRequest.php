<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BillingBalanceRequest extends FormRequest
{
    public function rules(): array
    {
        $billing = $this->route('billing_balance');

        return [
            'billing_date' => [
                'required', 'date',
                Rule::unique('billing_balances')
                    ->where('customer_id', $this->input('customer_id'))
                    ->where('is_deleted', 0)
                    ->ignore($billing?->id),
            ],
            'customer_id' => 'required|exists:customers,id',
            'prev_amount' => 'required|numeric|min:0',
            'sales_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'payment_amount' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'billing_date.required' => '請求日は必須です。',
            'billing_date.unique' => 'この得意先・請求日の組み合わせはすでに登録されています。',
            'customer_id.required' => '得意先は必須です。',
            'customer_id.exists' => '選択された得意先が存在しません。',
            'prev_amount.required' => '前月繰越額は必須です。',
            'prev_amount.numeric' => '前月繰越額は数値で入力してください。',
            'prev_amount.min' => '前月繰越額は0以上で入力してください。',
            'sales_amount.required' => '売上金額は必須です。',
            'sales_amount.numeric' => '売上金額は数値で入力してください。',
            'sales_amount.min' => '売上金額は0以上で入力してください。',
            'tax_amount.required' => '消費税は必須です。',
            'tax_amount.numeric' => '消費税は数値で入力してください。',
            'tax_amount.min' => '消費税は0以上で入力してください。',
            'payment_amount.required' => '入金額は必須です。',
            'payment_amount.numeric' => '入金額は数値で入力してください。',
            'payment_amount.min' => '入金額は0以上で入力してください。',
        ];
    }
}
