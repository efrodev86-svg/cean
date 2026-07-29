<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMateriaRequest extends FormRequest
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
            'licenciatura_id' => ['required', 'exists:licenciaturas,id'],
            'clave' => [
                'required',
                'string',
                'max:20',
                Rule::unique('materias', 'clave')->where(fn ($q) => $q->where('licenciatura_id', $this->input('licenciatura_id'))),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'semestre' => ['required', 'integer', 'min:1', 'max:12'],
            'orden' => ['nullable', 'integer', 'min:1', 'max:30'],
            'creditos' => ['required', 'numeric', 'min:0', 'max:99'],
            'horas_semana' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'horas_semestre' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clave.required' => 'La clave es obligatoria.',
            'nombre.required' => 'El nombre es obligatorio.',
            'semestre.required' => 'El semestre es obligatorio.',
            'creditos.required' => 'Los créditos son obligatorios.',
        ];
    }
}
