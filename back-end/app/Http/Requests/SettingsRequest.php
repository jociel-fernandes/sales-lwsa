<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'input' => 'required|array',
            'input.*' => 'required|max:255',
            'value' => 'required|array',
            'value.*' => 'nullable',
            'label' => 'sometimes|array',
            'label.*' => 'nullable|max:255',
        ];
    }

    public function attributes()
    {
        return [
            'input' => 'Name',
            'value' => 'Valor',
            'label' => 'Label',
        ];
    }
}
