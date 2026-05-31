<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportarCalificacionesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bimestre' => ['required', 'integer', 'min:1', 'max:5'],
            'archivo' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bimestre.required' => 'Selecciona el bimestre.',
            'archivo.required' => 'Selecciona un archivo CSV.',
            'archivo.mimes' => 'El archivo debe ser CSV.',
        ];
    }
}
