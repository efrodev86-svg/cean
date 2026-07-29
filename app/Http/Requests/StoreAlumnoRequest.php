<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class StoreAlumnoRequest extends AlumnoRequest
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
            ...$this->reglasPerfilAlumno(),
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mensajesPerfilAlumno();
    }
}
