<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class UpdateAlumnoRequest extends AlumnoRequest
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
        $alumno = $this->route('alumno');

        return [
            ...$this->reglasPerfilAlumno($alumno?->id, $alumno?->user?->id),
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
