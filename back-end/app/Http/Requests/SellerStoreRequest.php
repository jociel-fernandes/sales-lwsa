<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SellerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute não pode ser vazio.',
            'string' => 'O campo :attribute deve ser um texto.',
            'min' => 'O campo :attribute não pode ter menos de :max caracteres.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'email' => 'O campo :attribute deve ser um email válido.',
            'exists' => 'O :attribute selecionado é inválido.',
            'unique' => 'O :attribute já está em uso.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', $this->input('cpf')),
            ]);
        }
    }

    public function attributes()
    {
        return [
            'name' => 'Nome',
            'email' => 'E-mail',
        ];
    }

    public function filters()
    {
        return [
            'name' => 'trim|strip_tags',
            'email' => 'trim|strip_tags|lowercase',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => 'false',
            'message' => 'Dados Inválidos na requisição',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
