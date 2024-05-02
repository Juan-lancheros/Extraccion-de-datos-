<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago_Proveedor extends Model
{
    use HasFactory;

    protected $table = 'pago_proveedor';

    protected $fillable = [
        'id_interno',
        'numero_transaccion',
        'numero_pago',
        'monto_total_pago',
        'fehca_pago',
        'ubicacion_factura',
        'transaccion_pagada',
        'monto_pagado_aplicado_factura',
        'cuenta(PRINCIPAL)'
    ];
}
