<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function rules(): array
    {
        $supplier = $this->route('supplier');

        return [
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('suppliers', 'code')->ignore($supplier?->id)->where('is_deleted', 0),
            ],
            'name'           => 'required|string|max:100',
            'name_kana'      => 'nullable|string|max:200|regex:/^[ァ-ヶー　 ]+$/u',
            'postal_code'    => 'nullable|string|max:8|regex:/^\d{3}-?\d{4}$/',
            'address'        => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'fax'            => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:100',
            'payment_site'   => 'nullable|integer|min:0|max:999',
            'remarks'        => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'        => '仕入先コードは必須です。',
            'code.max'             => '仕入先コードは20文字以内で入力してください。',
            'code.unique'          => 'この仕入先コードはすでに使用されています。',
            'name.required'        => '仕入先名は必須です。',
            'name.max'             => '仕入先名は100文字以内で入力してください。',
            'name_kana.max'        => '仕入先名カナは200文字以内で入力してください。',
            'name_kana.regex'      => '仕入先名カナは全角カタカナで入力してください。',
            'postal_code.regex'    => '郵便番号はXXX-XXXXの形式で入力してください。',
            'email.email'          => '有効なメールアドレスを入力してください。',
            'contact_person.max'   => '担当者名は100文字以内で入力してください。',
            'payment_site.integer' => '支払サイトは整数で入力してください。',
            'payment_site.min'     => '支払サイトは0以上で入力してください。',
            'payment_site.max'     => '支払サイトは999以内で入力してください。',
            'remarks.max'          => '備考は1000文字以内で入力してください。',
        ];
    }
}
