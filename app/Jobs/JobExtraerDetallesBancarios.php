<?php

namespace App\Jobs;

use App\Models\Detalles_Bancarios;
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

class JobExtraerDetallesBancarios implements ShouldQueue
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
            $resultado_cantidad = NetsuiteService::getRequest(2097, 'customsearch_axa_detalles_bancarios_prov', 0, 1, '', 'live');
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
                    $resultado = NetsuiteService::getRequest(2377, null, $start, $end, '', 'live');
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
                                $proveedor_principal             = empty($data[$j]["values"]["custrecord_2663_parent_vendor"]) ? null : $data[$j]["values"]["custrecord_2663_parent_vendor"][$y]["text"];
                                $nombre_empresa                  = empty($data[$j]["values"]["CUSTRECORD_2663_PARENT_VENDOR.companyname"]) ? null : $data[$j]["values"]["CUSTRECORD_2663_PARENT_VENDOR.companyname"];
                                $nombre                          = empty($data[$j]["values"]["name"]) ? null : $data[$j]["values"]["name"];
                                $tipo                            = empty($data[$j]["values"]["custrecord_2663_entity_bank_type"]) ? null : $data[$j]["values"]["custrecord_2663_entity_bank_type"][$y]["text"];
                                $formato_archivo_pago            = empty($data[$j]["values"]["custrecord_2663_entity_file_format"]) ? null : $data[$j]["values"]["custrecord_2663_entity_file_format"][$y]["text"];
                                $numero_cuenta_bancaria          = empty($data[$j]["values"]["custrecord_2663_entity_acct_no"]) ? null : $data[$j]["values"]["custrecord_2663_entity_acct_no"];
                                $tipo_cuenta_bancaria            = empty($data[$j]["values"]["custrecord_2663_entity_bank_acct_type"]) ? null : $data[$j]["values"]["custrecord_2663_entity_bank_acct_type"][$y]["text"];
                                $numero_bancario                 = empty($data[$j]["values"]["custrecord_2663_entity_bank_no"]) ? null : $data[$j]["values"]["custrecord_2663_entity_bank_no"];

    
                                if (isset($id_interno) && !empty($id_interno) && $id_interno != "") { 

                                    //validar si existe pedido
                                    $sql1_validar = Detalles_Bancarios::where('id_interno', $id_interno)
                                    ->where('nombre_empresa', $nombre_empresa)
                                    ->where('numero_cuenta_bancaria', $numero_cuenta_bancaria)
                                    ->where('nombre', $nombre)
                                        ->exists();
                                    if ($sql1_validar) {
                                        //actualiza

                                        Detalles_Bancarios::where('id_interno', $id_interno)
                                            ->update([
                                                'id_interno' => $id_interno,
                                                'proveedor_principal' => $proveedor_principal,
                                                'nombre_empresa' => $nombre_empresa,
                                                'nombre' => $nombre,
                                                'tipo' => $tipo,
                                                'formato_archivo_pago' => $formato_archivo_pago,
                                                'numero_cuenta_bancaria' => $numero_cuenta_bancaria,
                                                'tipo_cuenta_bancaria' => $tipo_cuenta_bancaria,
                                                'numero_bancario' => $numero_bancario,
                                            ]); 
                                        
                                        $valores_actualizados++;
                                    } else {
                                        //crea

                                        Detalles_Bancarios::create([
                                            'id_interno' => $id_interno,
                                            'proveedor_principal' => $proveedor_principal,
                                            'nombre_empresa' => $nombre_empresa,
                                            'nombre' => $nombre,
                                            'tipo' => $tipo,
                                            'formato_archivo_pago' => $formato_archivo_pago,
                                            'numero_cuenta_bancaria' => $numero_cuenta_bancaria,
                                            'tipo_cuenta_bancaria' => $tipo_cuenta_bancaria,
                                            'numero_bancario' => $numero_bancario,
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

