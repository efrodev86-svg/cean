<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultarBoletaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'matricula' => ['required', 'string', 'max:50'],
            'fecha_nacimiento' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'matricula.required' => 'Ingresa tu matrícula.',
            'fecha_nacimiento.required' => 'Ingresa tu fecha de nacimiento.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
        ];
    }
}
