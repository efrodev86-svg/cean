<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCicloRequest extends FormRequest
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
        $ciclo = $this->route('ciclo');

        return [
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('ciclos_escolares', 'nombre')
                    ->where('sede_id', $ciclo?->sede_id)
                    ->ignore($ciclo?->id),
            ],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del ciclo es obligatorio.',
            'nombre.unique' => 'Ya existe un ciclo escolar con ese nombre.',
        ];
    }
}
