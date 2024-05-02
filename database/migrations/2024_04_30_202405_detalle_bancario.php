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
        Schema::create('detalle_bancario', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('id_interno');
            $table->integer('proveedor_principal');
            $table->string('nombre_empresa');
            $table->string('nombre');
            $table->string('tipo');
            $table->string('formato_archivo_pago');
            $table->integer('numero_cuenta_bancaria');
            $table->string('tipo_cuenta_bancaria');
            $table->integer('numero_bancario');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_bancario');
    }
};
