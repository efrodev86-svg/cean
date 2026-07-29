<?php

namespace App\Http\Requests;

class StoreGrupoRequest extends GrupoRequest
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
        return $this->reglasGrupo();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mensajesGrupo();
    }
}
