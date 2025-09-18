<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SellerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $userId = $this->route('seller') ? $this->route('seller')->user_id : null;
        return [
            'name' => 'required|string|max:255',
            'email' => ['nullable','email','max:255', Rule::unique('users','email')->ignore($userId, 'id')],
            'password' => 'nullable|string|min:8|confirmed',
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
            'password' => 'trim|strip_tags',
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
