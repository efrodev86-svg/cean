<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudioCursado extends Model
{
    protected $table = 'estudios_cursados';

    protected $fillable = [
        'user_id',
        'descripcion',
        'documento_probatorio',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
