<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class StoreDocenteRequest extends DocenteRequest
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
        $reglas = [
            ...$this->reglasPerfilDocente(),
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
        return $this->mensajesPerfilDocente();
    }
}
