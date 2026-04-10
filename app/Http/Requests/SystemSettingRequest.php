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
            'closing_ym' => ['required', 'date_format:Y-m'],
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
