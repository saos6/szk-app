<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryBalanceRequest extends FormRequest
{
    public function rules(): array
    {
        $model = $this->route('inventory_balance');

        return [
            'stock_ym' => [
                'required',
                'string',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
                Rule::unique('inventory_balances', 'stock_ym')
                    ->where('warehouse_code', $this->input('warehouse_code'))
                    ->where('vehicle_model_code', $this->input('vehicle_model_code'))
                    ->where('frame_no', $this->input('frame_no'))
                    ->where('is_deleted', 0)
                    ->ignore($model?->id),
            ],
            'warehouse_code' => [
                'required', 'string', 'max:20',
                Rule::exists('warehouses', 'code')->where('is_deleted', 0),
            ],
            'vehicle_model_code' => [
                'required', 'string', 'max:8',
                Rule::exists('vehicle_models', 'kisyu_cd')->where('is_deleted', 0),
            ],
            'frame_no'   => 'required|string|max:10',
            'prev_stock' => 'required|integer|min:0',
            'in_stock'   => 'required|integer|min:0',
            'out_stock'  => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'stock_ym.required'             => '年月は必須です。',
            'stock_ym.regex'                => '年月は YYYY-MM 形式で入力してください。',
            'stock_ym.unique'               => 'この年月・倉庫・機種・フレームNoの組み合わせはすでに登録されています。',
            'warehouse_code.required'       => '倉庫コードは必須です。',
            'warehouse_code.exists'         => '選択された倉庫が存在しません。',
            'vehicle_model_code.required'   => '機種コードは必須です。',
            'vehicle_model_code.exists'     => '選択された機種コードが存在しません。',
            'frame_no.required'             => 'フレームNoは必須です。',
            'frame_no.max'                  => 'フレームNoは10文字以内で入力してください。',
            'prev_stock.required'           => '前月繰越在庫数は必須です。',
            'prev_stock.integer'            => '前月繰越在庫数は整数で入力してください。',
            'prev_stock.min'                => '前月繰越在庫数は0以上で入力してください。',
            'in_stock.required'             => '当月入庫数は必須です。',
            'in_stock.integer'              => '当月入庫数は整数で入力してください。',
            'in_stock.min'                  => '当月入庫数は0以上で入力してください。',
            'out_stock.required'            => '当月出庫数は必須です。',
            'out_stock.integer'             => '当月出庫数は整数で入力してください。',
            'out_stock.min'                 => '当月出庫数は0以上で入力してください。',
        ];
    }
}
