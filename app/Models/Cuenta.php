<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $table = 'cuenta';

    protected $fillable = [
        'id_interno',
        'numero_cuenta',
        'fecha',
        'documento',
        'proveedor',
        'tipo_documento',
        'numero_documento',
        'estado',
        'descripcion',
        'importe_(debito)',
        'importe_(credito)',
        'valor',
        'saldo',
        'cuenta',
        'formula(fecha/hora)'
    ];

}
