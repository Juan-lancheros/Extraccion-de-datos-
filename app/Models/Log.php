<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = 'log';

    protected $fillable = [
        'proceso',
        'tabla',
        'cant_registro',
        'cant_insertados',
        'mensaje',
        'cant_actualizados',
        'fecha',
        'fecha_inicio',
        'fecha_fin',
        'tiempo'
    ];
}
