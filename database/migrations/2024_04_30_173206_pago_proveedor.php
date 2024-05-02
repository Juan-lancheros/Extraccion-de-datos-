<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pago_proveedor', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('id_interno');
            $table->integer('numero_transaccion');
            $table->integer('numero_pago');
            $table->float('monto_total_pago');
            $table->dateTime('fehca_pago');
            $table->string('ubicacion_factura');
            $table->float('transaccion_pagada');
            $table->float('monto_pagado_aplicado_factura');
            $table->string('cuenta(PRINCIPAL)');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_proveedor');
    }
};
