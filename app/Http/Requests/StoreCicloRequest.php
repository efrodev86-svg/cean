<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCicloRequest extends FormRequest
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
            'sede_id' => ['required', 'integer', 'exists:sedes,id'],
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('ciclos_escolares', 'nombre')->where('sede_id', $this->input('sede_id')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sede_id.required' => 'Selecciona la sede del ciclo.',
            'sede_id.exists' => 'La sede seleccionada no existe.',
            'nombre.required' => 'El nombre del ciclo es obligatorio.',
            'nombre.unique' => 'Esa sede ya tiene un ciclo con ese nombre.',
        ];
    }
}
