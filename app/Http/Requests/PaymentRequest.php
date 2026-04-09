<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'          => ['required', 'exists:customers,id'],
            'employee_id'          => ['nullable', 'exists:employees,id'],
            'payment_date'         => ['required', 'date'],
            'subject'              => ['required', 'string', 'max:200'],
            'status'               => ['required', Rule::in(array_keys(Payment::STATUSES))],
            'remarks'              => ['nullable', 'string'],

            'items'                => ['required', 'array', 'min:1'],
            'items.*.payment_type' => ['required', Rule::in(array_keys(Payment::PAYMENT_TYPES))],
            'items.*.amount'       => ['required', 'numeric', 'min:0'],
            'items.*.bank_info'    => ['nullable', 'string', 'max:200'],
            'items.*.remarks'      => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required'          => '得意先は必須です。',
            'customer_id.exists'            => '得意先が存在しません。',
            'employee_id.exists'            => '担当者が存在しません。',
            'payment_date.required'         => '入金日は必須です。',
            'payment_date.date'             => '入金日の形式が正しくありません。',
            'subject.required'              => '件名は必須です。',
            'subject.max'                   => '件名は200文字以内で入力してください。',
            'status.required'               => 'ステータスは必須です。',
            'status.in'                     => 'ステータスが正しくありません。',
            'items.required'                => '明細を1行以上入力してください。',
            'items.min'                     => '明細を1行以上入力してください。',
            'items.*.payment_type.required' => '入金区分は必須です。',
            'items.*.payment_type.in'       => '入金区分が正しくありません。',
            'items.*.amount.required'       => '入金額は必須です。',
            'items.*.amount.min'            => '入金額は0以上の値を入力してください。',
        ];
    }
}
