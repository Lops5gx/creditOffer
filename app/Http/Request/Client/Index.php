<?php

namespace App\Http\Request\Client;

use App\Utils\Utils;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Index extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; //Do not have users to validate
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'cpf' => 'required|string|max:11'
        ];
    }

    protected function prepareForValidation(){
    
        $cpf = '';

        if($this->cpf){
            $cpf = trim($this->cpf);
            $cpf =str_replace(array('.','-','/'), "", $cpf);
        }

        $this->merge([
            'cpf' => $cpf,
        ]);
    }

    public function messages(): array
    {
        return [
            'cpf.required' => 'CPF inválido ou não informado.',
            'cpf.max ' => 'CPF deve conter :max dígitos ',
        ];
    }
    
    protected function failedValidation(Validator $validator)
    { 
        throw new HttpResponseException(response()->json($validator->errors(), Utils::STATUS_CODE_400, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],JSON_UNESCAPED_UNICODE)); 
    }
}