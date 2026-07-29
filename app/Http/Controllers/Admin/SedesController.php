<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSedeRequest;
use App\Http\Requests\UpdateSedeRequest;
use App\Models\Sede;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SedesController extends Controller
{
    public function index(): View
    {
        $sedes = Sede::query()
            ->withCount('ciclos')
            ->with('encargados')
            ->orderBy('nombre')
            ->get();

        return view('admin.sedes.index', [
            'sedes' => $sedes,
        ]);
    }

    public function store(StoreSedeRequest $request): RedirectResponse
    {
        $datos = collect($request->validated())->except('logo')->all();

        $sede = Sede::query()->create([
            ...$datos,
            'activa' => true,
        ]);

        if ($request->hasFile('logo')) {
            $sede->update(['logo' => $this->guardarLogo($request->file('logo'), $sede)]);
        }

        return redirect()
            ->route('admin.sedes.index')
            ->with('success', 'Sede registrada correctamente.');
    }

    public function update(UpdateSedeRequest $request, Sede $sede): RedirectResponse
    {
        $datos = collect($request->validated())->except('logo')->all();
        $datos['activa'] = (bool) ($datos['activa'] ?? false);

        if ($request->hasFile('logo')) {
            $datos['logo'] = $this->guardarLogo($request->file('logo'), $sede);
        }

        $sede->update($datos);

        return redirect()
            ->route('admin.sedes.index')
            ->with('success', 'Sede actualizada correctamente.');
    }

    private function guardarLogo(UploadedFile $archivo, Sede $sede): string
    {
        $directorio = public_path('images/sedes');

        if (! is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $this->eliminarLogoAnterior($sede);

        $extension = $archivo->guessExtension() ?: 'png';
        $nombre = Str::slug($sede->clave).'.'.$extension;
        $archivo->move($directorio, $nombre);

        return 'images/sedes/'.$nombre;
    }

    private function eliminarLogoAnterior(Sede $sede): void
    {
        if (! $sede->logo || ! str_starts_with($sede->logo, 'images/sedes/')) {
            return;
        }

        $ruta = public_path($sede->logo);

        if (is_file($ruta)) {
            unlink($ruta);
        }
    }
}
