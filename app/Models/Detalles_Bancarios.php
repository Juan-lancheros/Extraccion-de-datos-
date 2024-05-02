<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalles_Bancarios extends Model
{
    use HasFactory;

    protected $table = 'detalle_bancario';

    protected $fillable = [
        'id_interno',
        'proveedor_principal',
        'nombre_empresa',
        'nombre',
        'tipo',
        'formato_archivo_pago',
        'numero_cuenta_bancaria',
        'tipo_cuenta_bancaria',
        'numero_bancario'
    ];
}
