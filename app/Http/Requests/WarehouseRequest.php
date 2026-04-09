<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:100',
        ];

        if ($this->isMethod('POST')) {
            $rules['code'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('warehouses', 'code')->where('is_deleted', false),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'code.required'  => '倉庫コードは必須です。',
            'code.max'       => '倉庫コードは20文字以内で入力してください。',
            'code.unique'    => 'この倉庫コードはすでに使用されています。',
            'name.required'  => '倉庫名は必須です。',
            'name.max'       => '倉庫名は100文字以内で入力してください。',
        ];
    }
}
