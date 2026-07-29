<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradoAcademicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isControlEscolar() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('abreviatura') !== null) {
            $this->merge(['abreviatura' => trim((string) $this->input('abreviatura'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'abreviatura' => ['required', 'string', 'max:20', 'unique:grados_academicos,abreviatura'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'abreviatura.required' => 'La abreviatura es obligatoria (ej. Dr., Mtro.).',
            'abreviatura.unique' => 'Esa abreviatura ya está registrada.',
        ];
    }
}
