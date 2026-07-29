<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEstudioCursadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isControlEscolar() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['descripcion', 'documento_probatorio'] as $campo) {
            if ($this->has($campo)) {
                $merge[$campo] = trim((string) $this->input($campo));
            }
        }

        if ($this->input('documento_probatorio') === '') {
            $merge['documento_probatorio'] = null;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'descripcion' => ['required', 'string', 'max:255'],
            'documento_probatorio' => ['nullable', 'string', 'max:255'],
            'fecha' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'descripcion.required' => 'La descripción es obligatoria.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha no es válida.',
        ];
    }
}
