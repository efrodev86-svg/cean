<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderMateriasRequest extends FormRequest
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
            'semestre' => ['required', 'integer', 'min:1', 'max:12'],
            'materias' => ['required', 'array', 'min:1'],
            'materias.*' => [
                'integer',
                Rule::exists('materias', 'id')->where(function ($query) {
                    $query->where('licenciatura_id', $this->input('licenciatura_id'))
                        ->where('semestre', $this->input('semestre'));
                }),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'materias.required' => 'Debes indicar el orden de las materias.',
            'materias.*.exists' => 'Una o más materias no pertenecen a esta carrera y semestre.',
        ];
    }
}
