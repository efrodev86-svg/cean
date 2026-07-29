<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportarCalificacionesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isControlEscolar() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sede_id' => ['nullable', 'integer', 'exists:sedes,id'],
            'semestre' => ['required', 'integer', 'min:1', 'max:12'],
            'archivo' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'semestre.required' => 'Selecciona el semestre.',
            'archivo.required' => 'Selecciona un archivo CSV.',
            'archivo.mimes' => 'El archivo debe ser CSV.',
        ];
    }
}
