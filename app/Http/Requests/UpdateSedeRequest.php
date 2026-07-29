<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        foreach (['escuela', 'director', 'ciudad'] as $campo) {
            if ($this->input($campo) === '') {
                $this->merge([$campo => null]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $sedeId = $this->route('sede')?->id;

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'clave' => ['required', 'string', 'max:30', Rule::unique('sedes', 'clave')->ignore($sedeId)],
            'escuela' => ['nullable', 'string', 'max:150'],
            'director' => ['nullable', 'string', 'max:150'],
            'ciudad' => ['nullable', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'activa' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la sede es obligatorio.',
            'clave.required' => 'La clave (CCT) de la sede es obligatoria.',
            'clave.unique' => 'Ya existe una sede con esa clave.',
        ];
    }
}
