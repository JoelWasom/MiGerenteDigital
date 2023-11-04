<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Controllers\bitacoraController;

class cjACController extends Controller
{
    public function usuarioTieneCajaAbierta(Request $request)
    {
       
        $cajId = $request->cajId;
        $acFechaApertura =$request->acFechaApertura;
        try {
            // Verificar si existe un registro de apertura de caja para el usuario y la caja en el día actual
            $aperturaCajaExistente = DB::table('cjAperturaCierre')
                ->where('cajId', $cajId)
                ->whereDate('acFechaApertura', now()->toDateString())
                ->first();
        //   echo now()->toDateString();

     
            if ($aperturaCajaExistente == null) {
                return response()->json(['mensaje' => 'La caja Ventas no Fue Aperturada'],201);
                // Hay una caja aperturada
            } 
        
        } catch (\Exception $e) {
            // Manejo de la excepción si ocurre un error en la consulta
            return ['error'=>'Error al verificar la apertura de caja: ' . $e->getMessage(),400];
        }
    }
    public function AperturaCaja(Request $request)
    {
        try {
            $mensajesErrores = [
                'cajId.numeric' => 'El campo ID de caja debe ser un numero.',
                'cajId.required' => 'El campo ID de caja es requerido.',
                'userId.required' => 'El campo Usuario es requerido.',
                'userId.numeric' => 'El campo ID usuario debe ser un numero .',
                'acMontoApertura.numeric' => 'El campo Monto de Apertura debe ser un número.',
                'acMontoApertura.required' => 'El campo Monto de Apertura es requerido.',
                'acFechaApertura.date' => 'El campo Fecha de Apertura debe ser una fecha válida.',
                'acActivo.numeric' => 'El campo Activo es requerido.',
            ];

            // Validación de campos
            $datosValidados = $request->validate([
                'cajId' => 'required|numeric',
                'userId' => 'required|numeric',
                'acMontoApertura' => 'required|numeric',
                'acFechaApertura' => 'date',
                'acActivo' => 'numeric',
            ], $mensajesErrores);

            // Verificar si ya se abrió la caja en el mismo día
            $cajaAbiertaHoy = DB::table('cjAperturaCierre')
                ->where('cajId', $datosValidados['cajId'])
                // ->where('userId', $datosValidados['userId'])//quitar esto ya que todo ingreso Hira a una mis caja
                ->whereDate('acFechaApertura', now()->toDateString())
                ->first();
            if ($cajaAbiertaHoy) {

                return response()->json(['error' => 'Ya se Realizo la Apertura'], 400);
            }


            DB::beginTransaction();
            // Insertar el nuevo registro en la tabla cjAperturaCierre
            $acId = DB::table('cjAperturaCierre')->insertGetId([
                'cajId' => $datosValidados['cajId'],
                'userId' => $datosValidados['userId'],
                'acMontoApertura' => $datosValidados['acMontoApertura'],
                'acFechaApertura' => DB::raw('CURRENT_TIMESTAMP'),
                'acActivo' => $datosValidados['acActivo'],
                'acFechaCreacion' => DB::raw('CURRENT_TIMESTAMP'),
            ]);

            // Agregar el registro en la tabla bitácora
            $bitacora = new bitacoraController();
            $bitacora->insertarBitacora('cjAperturaCierre', $acId, $datosValidados['userId'], 'Nueva Apertura de Caja', 'Se ha registrado una nueva apertura de caja con ID: ' . $acId);
            DB::commit();
            // Retornar una respuesta de éxito
            return response()->json(['mensaje' => 'Apertura de caja registrada exitosamente'], 201);
        } catch (\Exception $e) {
            // Manejo de la excepción
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar la apertura de caja: ' . $e->getMessage()], 500);
        }
    }
    public function cerrarCaja(Request $request)
    {
        try {
            $mensajesErrores = [
                'cajId.required' => 'El campo ID de caja es requerido.',
                'userId.required' => 'El campo Usuario es requerido.',
                // 'acMontoCierre.required' => 'El campo Monto de Cierre es requerido.',
                // 'acFechaCierre.required' => 'El campo Fecha de Cierre es requerido.',
                'acActivo.required' => 'El campo Activo es requerido.',
            ];

            // Validación de campos
            $datosValidados = $request->validate([
                'cajId' => 'required|numeric',
                'userId' => 'required|numeric',
                // 'acMontoCierre' => 'required|numeric',
                // 'acFechaCierre' => 'required|date',
                'acActivo' => 'required',
            ], $mensajesErrores);


            // Verificar si existe un registro de cierre activo para esta caja en la fecha actual
            $registroCierre = DB::table('cjAperturaCierre')
                ->where('cajId', $datosValidados['cajId'])
                ->whereDate('acFechaCreacion', now()->toDateString()) // Compara con la fecha actual
                ->where('acMontoApertura', '>', 0)
                ->where('acMontoCierre', '>', 0)
                ->where('acActivo', 1)
                ->first();

            if ($registroCierre) {
                DB::rollBack();
                return response()->json(['error' => 'Ya se Realizo el Cierre de la caja'], 400);
            }

            DB::beginTransaction();

            // Obtener el monto actual de la apertura de caja
            $montoApertura = DB::table('cjAperturaCierre')
                ->where('cajId', $datosValidados['cajId'])
                ->where('acActivo', 1)
                ->value('acMontoApertura');

            if ($montoApertura === null) {
                DB::rollBack();
                return response()->json(['error' => 'No se encontró una apertura de caja activa para la caja especificada'], 400);
            }



            // Sumar el monto de las transacciones de venta (ttxnId = 1) para esa caja
            $montoVentas = DB::table('vnttxn')
                ->join('cjttxn', 'vnttxn.userId', '=', 'cjttxn.userId')
                ->join('vndettxn', 'vnttxn.vntId', '=', 'vndettxn.vntid')
                ->where('cjttxn.cajId', $datosValidados['cajId'])
                ->where('vnttxn.ttxId', 1) // Suponiendo que 1 es el ID del tipo de transacción de venta
                ->where('vnttxn.vntActivo', 1)
                ->sum('vndettxn.vndPrecioVenta');

            echo $montoVentas;
            // Calcular el monto de cierre sumando el monto de apertura y las ventas
            $montoCierre = $montoApertura + $montoVentas;

            // Actualizar el registro de cierre de caja en la tabla cjAperturaCierre
            DB::table('cjAperturaCierre')
                ->where('cajId', $datosValidados['cajId'])
                ->where('acActivo', 1)
                ->update([
                    'acMontoCierre' => $montoCierre,
                    'acFechaCierre' => DB::raw('CURRENT_TIMESTAMP'),
                    'acActivo' => 1,
                ]);

            // Agregar el registro en la tabla bitácora
            $bitacora = new bitacoraController();
            $bitacora->insertarBitacora('cjAperturaCierre', $datosValidados['cajId'], $datosValidados['userId'], 'Cierre de Caja', 'Se ha realizado el cierre de caja para la caja con ID: ' . $datosValidados['cajId']);

            DB::commit();

            // Retornar una respuesta de éxito
            return response()->json(['mensaje' => 'Cierre de caja realizado con éxito'], 200);
        } catch (\Exception $e) {
            // Manejo de la excepción
            DB::rollBack();
            return response()->json(['error' => 'Error al realizar el cierre de caja: ' . $e->getMessage()], 500);
        }
    }
    public function obtenerAperturasCaja()
    {
        try {
            $aperturasCaja = DB::table('cjAperturaCierre AS ac')
                ->select('ac.*', 'caj.cajNombre AS nombre_caja', 'usr.name AS nombre_usuario')
                ->join('cjCaja AS caj', 'ac.cajId', '=', 'caj.cajId')
                ->join('users AS usr', 'ac.userId', '=', 'usr.Id')
                ->where('ac.acActivo', 1)
                ->get();

            return response()->json($aperturasCaja, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las aperturas de caja: ' . $e->getMessage()], 500);
        }
    }
}
