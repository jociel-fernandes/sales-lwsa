<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        return $user->hasRole('admin') || $user->hasRole('sellers');
    }

    public function rules(): array
    {
        $user = $this->user();
        $rules = [
            'date' => ['required','date','before_or_equal:today'],
            'value' => ['required','numeric','min:0.01'],
            'description' => ['nullable','string','max:2000'],
        ];

        if ($user && $user->hasRole('admin')) {
            $rules['seller_id'] = ['required','exists:sellers,id'];
        };

        return $rules;
    }
}
