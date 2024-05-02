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
        Schema::create('cuenta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_interno');
            $table->integer('numero_cuenta');
            $table->dateTime('fecha');
            $table->integer('documento');
            $table->string('proveedor');
            $table->string('tipo_documento');
            $table->string('numero_documento');
            $table->string('estado');
            $table->string('descripcion');
            $table->float('importe_(debito)');
            $table->float('importe_(credito)');
            $table->float('valor');
            $table->float('saldo');
            $table->string('cuenta');
            $table->string('formula(fecha/hora)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuenta');
    }
};
