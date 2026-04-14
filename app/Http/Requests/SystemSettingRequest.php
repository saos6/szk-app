<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SystemSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'closing_ym'        => ['required', 'date_format:Y-m'],
            'company_name'      => ['nullable', 'string', 'max:100'],
            'company_name_kana' => ['nullable', 'string', 'max:100'],
            'postal_code'       => ['nullable', 'string', 'max:10'],
            'prefecture_city'   => ['nullable', 'string', 'max:100'],
            'address'           => ['nullable', 'string', 'max:200'],
            'building'          => ['nullable', 'string', 'max:200'],
            'representative'    => ['nullable', 'string', 'max:100'],
            'tel'               => ['nullable', 'string', 'max:20'],
            'fax'               => ['nullable', 'string', 'max:20'],
            'invoice_no'        => ['nullable', 'string', 'max:20'],
            'bank_info'         => ['nullable', 'string', 'max:200'],
            'account_number'    => ['nullable', 'string', 'max:50'],
            'account_holder'    => ['nullable', 'string', 'max:100'],
            'remarks'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'closing_ym.required'    => '月次更新年月は必須です。',
            'closing_ym.date_format' => '月次更新年月は YYYY-MM 形式で入力してください。',
        ];
    }
}
