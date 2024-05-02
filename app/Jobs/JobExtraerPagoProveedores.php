<?php

namespace App\Jobs;

use App\Models\Pago_Proveedor;
use App\Models\Log;
use App\Services\NetsuiteService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log as LogFacade;
use PhpParser\Node\Stmt\TryCatch;
use Throwable;

class JobExtraerPagoProveedores implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            date_default_timezone_set('America/Bogota');
            $valores_creados = 0;
            $valores_actualizados = 0;

            //tiempo
            $tiempo_inicio = $this->microtime_float();
            $fecha_LOG = date('Y-m-d');
            $fecha_inicio_log = Date('Y-m-d\TH:i:s');
            // Primera consulta a netsuite, consultar cantidades
            $resultado_cantidad = NetsuiteService::getRequest(2097, 'customsearch_axa_pago_proveedor', 0, 1, '', 'live');
            LogFacade::error("message: " . $resultado_cantidad);
            Log::insert([
                'proceso' => "Extraccion estado cuenta proveedores ",
                'tabla' => 'ninguna',
                'mensaje' => 'Cantidad de Registros - ' . $resultado_cantidad,
            ]);
            if (intval($resultado_cantidad) > 0) {
                $start = 0;
                $end = 0;
                $cantidad_for = ceil(intval($resultado_cantidad) / 1000) * 1000;
                $cantidad_dividir = intval($cantidad_for / 1000);
                for ($i = 0; $i < $cantidad_dividir; $i++) {
                    $start = ($i == 0) ? 0 : $start + 1000;
                    $end = $end + 1000;
                    $start = str_pad($start, mb_strlen($end), "0", STR_PAD_LEFT);
                    $start = intval($start); // Convertir "09000" a un número entero
                    $dateStart = Carbon::now('America/Bogota');
                    $messageError = "";
                    $resultado = NetsuiteService::getRequest(2376, null, $start, $end, '', 'live');
                    $data = json_decode($resultado, true);
                    $length = 0;
                    if (is_array($data)) {
                        $length = count($data);
                    }
                    var_dump($length);
                    if (isset($data['error'])) {
                        $messageError .= json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . " ";
                    } else {
                        if (count($data) > 0) {
                            for ($j = 0; $j < count($data); $j++) {
                                $y = 0;
                         
                                $id_interno                      = $data[$j]["id"];
                                $numero_transaccion              = empty($data[$j]["values"]["transactionnumber"]) ? null : $data[$j]["values"]["transactionnumber"];
                                $numero_pago                     = empty($data[$j]["values"]["transactionname"]) ? null : $data[$j]["values"]["transactionname"];
                                $monto_total_pago                = empty($data[$j]["values"]["amount"]) ? null : $data[$j]["values"]["amount"];
                                $fehca_pago                      = empty($data[$j]["values"]["trandate"]) ? null : $data[$j]["values"]["trandate"];
                                $ubicacion_factura               = empty($data[$j]["values"]["appliedToTransaction.location"]) ? null : $data[$j]["values"]["appliedToTransaction.location"][$y]["text"];
                                $transaccion_pagada              = empty($data[$j]["values"]["appliedToTransaction.tranid"]) ? null : $data[$j]["values"]["appliedToTransaction.tranid"];
                                $monto_pagado_aplicado_factura   = empty($data[$j]["values"]["appliedtolinkamount"]) ? null : $data[$j]["values"]["appliedtolinkamount"];
                                $cuenta                          = empty($data[$j]["values"]["accountmain"]) ? null : $data[$j]["values"]["accountmain"][$y]["text"];

    
                                if (isset($id_interno) && !empty($id_interno) && $id_interno != "") { 

                                    //validar si existe pedido
                                    $sql1_validar = Pago_Proveedor::where('id_interno', $id_interno)
                                    ->where('numero_transaccion', $numero_transaccion)
                                    ->where('transaccion_pagada', $transaccion_pagada)
                                    ->where('numero_pago', $numero_pago)
                                        ->exists();
                                    if ($sql1_validar) {
                                        //actualiza

                                             Pago_Proveedor::where('id_interno', $id_interno)
                                            ->update([
                                                'id_interno' => $id_interno,
                                                'numero_transaccion' => $numero_transaccion,
                                                'numero_pago' => $numero_pago,
                                                'monto_total_pago' => $monto_total_pago,
                                                'fehca_pago' => $fehca_pago,
                                                'ubicacion_factura' => $ubicacion_factura,
                                                'transaccion_pagada' => $transaccion_pagada,
                                                'monto_pagado_aplicado_factura' => $monto_pagado_aplicado_factura,
                                                'cuenta(PRINCIPAL)' => $cuenta,
                                            ]); 
                                        
                                        $valores_actualizados++;
                                    } else {
                                        //crea

                                            Pago_Proveedor::create([
                                            'id_interno' => $id_interno,
                                            'numero_transaccion' => $numero_transaccion,
                                            'numero_pago' => $numero_pago,
                                            'monto_total_pago' => $monto_total_pago,
                                            'fehca_pago' => $fehca_pago,
                                            'ubicacion_factura' => $ubicacion_factura,
                                            'transaccion_pagada' => $transaccion_pagada,
                                            'monto_pagado_aplicado_factura' => $monto_pagado_aplicado_factura,
                                            'cuenta(PRINCIPAL)' => $cuenta,
                                        ]);

                                        $valores_creados++;  
                                    }
                                } else {
                                    $messageError .= "No existe ese valor interno del id en la base - ";
                                }

                            }
                        } else {
                            $messageError .= "No se encontraron resultados";
                        }
                    }
                    // Guardar log de error
                    if ($messageError) {
                        Log::create([
                            'proceso' => "Error en NetSuite al consumir NetsuiteService desde el JOB cuenta entre {$start} y {$end}",
                            'tabla' => 'ninguna',
                            'mensaje' => $messageError,
                            'fecha' => Carbon::now('America/Bogota'),
                            'created_at' => Carbon::now('America/Bogota')
                        ]);
                    }

                    if (!isset($data['error'])) {

                        Log::create([
                            'proceso' => "Resultado extracción estado cuenta proveedores {$start} y {$end}",
                            'tabla' => 'ninguna',
                            'mensaje' => "Se crearon $valores_creados y se actualizaron $valores_actualizados en la tabla cuenta",
                            'fecha' => Carbon::now('America/Bogota'),
                            'fecha_inicio' => $dateStart,
                            'fecha_fin' => Carbon::now('America/Bogota'),
                            'created_at' => Carbon::now('America/Bogota')
                        ]);
                    }
                }
            }
            $fecha_fin_log = Date('Y-m-d\TH:i:s');

            $tiempo_fin = $this->microtime_float();
            $tiempo_a = $tiempo_fin - $tiempo_inicio;
            $tiempo = $tiempo_a / 60;

            //crear log
            $sql_log = Log::create([
                'proceso' => "REGISTRO DESDE QUEUES",
                'tabla' => 'ExtraccionData',
                'cant_registro' => intval($resultado_cantidad),
                'cant_insertados' => $valores_creados,
                'cant_actualizados' => $valores_actualizados,
                'fecha' => $fecha_LOG,
                'fecha_inicio' => $fecha_inicio_log,
                'fecha_fin' => $fecha_fin_log,
                'tiempo' => $tiempo
            ]);
            echo "Finalizo extraccion \n";
        } catch (Throwable $exception) {
            //dd($exception->getMessage(),$resultado);
            $dateStart = Carbon::now('America/Bogota');
            Log::insert([
                'proceso' => "Error en el JOB cuenta",
                'tabla' => 'ninguna',
                'mensaje' => $exception,
                'fecha' => Carbon::now('America/Bogota'),
                'fecha_inicio' => $dateStart,
                'fecha_fin' => Carbon::now('America/Bogota'),
            ]);
            $this->release(30);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $dateStart = Carbon::now('America/Bogota');
        Log::insert([
            'proceso' => "Error en el JOB facturas",
            'tabla' => 'ninguna',
            'mensaje' => $exception,
            'fecha' => Carbon::now('America/Bogota'),
            'fecha_inicio' => $dateStart,
            'fecha_fin' => Carbon::now('America/Bogota'),
        ]);
    }

    function microtime_float()
    {
        list($useg, $seg) = explode(" ", microtime());
        return ((float)$useg + (float)$seg);
    }

}
