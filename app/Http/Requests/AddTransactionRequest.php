<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date' => 'required',
            'account' => 'required',
            'payed_amount' => 'required',
            'real_amount' => '',
            'description' => 'required',
            'detail' => '',
            'memo' => '',
            'category' => 'required',
            'subcategory' => ''
        ];
    }
}
