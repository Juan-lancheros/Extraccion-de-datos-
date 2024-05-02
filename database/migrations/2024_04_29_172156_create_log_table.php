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
        Schema::create('log', function (Blueprint $table) {
            $table->id();
            $table->string('proceso');
            $table->string('tabla');
            $table->text('mensaje')->nullable();
            $table->integer('cant_registro')->default(0)->nullable();
            $table->integer('cant_insertados')->default(0)->nullable();
            $table->integer('cant_actualizados')->default(0)->nullable();
            $table->date('fecha')->nullable();
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->text('tiempo')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log');
    }
};
