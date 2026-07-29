<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateDocenteRequest extends DocenteRequest
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
        $docenteId = $this->route('docente')?->id;

        $reglas = [
            ...$this->reglasPerfilDocente($docenteId),
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($docenteId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];

        if ($this->user()?->isAdmin()) {
            $reglas['sedes'] = ['required', 'array', 'min:1'];
            $reglas['sedes.*'] = ['integer', 'exists:sedes,id'];
        }

        return $reglas;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->mensajesPerfilDocente(),
            'email.unique' => 'Ya existe un usuario con ese correo.',
        ];
    }
}
