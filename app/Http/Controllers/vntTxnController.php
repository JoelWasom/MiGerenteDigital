<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Controllers\bitacoraController;
use App\Http\Controllers\InDetTxnController;
use App\Http\Controllers\InvTxnController;
use App\Http\Controllers\vnDetTxnController;
use App\Http\Controllers\intArticuloController;
use App\Http\Controllers\cjtTxnController;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TryCatch;
use Psy\Readline\HoaConsole;

class vntTxnController extends Controller
{
    public function guardarVenta(Request $request)
    {
        DB::beginTransaction();
        try {
            $cliId = $request->cliId;
            $userId = $request->userId;
            $ttxId = $request->ttxId;
            // $vntNumero = $request->vntNumero;
            $vntCredito = $request->vntCredito;
            $fpId = $request->fpId;
            $vntActivo = $request->vntActivo;
            // $vntFechaCreacion = $request->vntFechaCreacion;

            
            // Verificar si existen ventas 
            $vntNumero = DB::table('vntTxn')->max('vntNumero');

            if ($vntNumero === null) {
                // No hay ventas, generar el primer número de venta
                $nuevoVntNumero = 'VE000001';
            } else {
                // Generar el nuevo número de venta en el formato "VE000000"
                $nuevoVntNumero = 'VE' . str_pad((int)substr($vntNumero, 2) + 1, 6, '0', STR_PAD_LEFT);
            }

            // Obtener detalles de los productos vendidos//
            // $detallesVenta = $request->detallesVenta;   //esto se descomenta para probar en postman
            $detallesVenta = json_decode($request->input('detallesVenta'), true);

            // Objetos
            $obj_invTxn = new InvTxnController();
            $vnDetTxnController = new vnDetTxnController();
            $bitacoraController = new bitacoraController();
            $intArticuloController = new intArticuloController();
            $obj_cjttxn = new cjtTxnController();
            // Validar la cantidad disponible antes de guardar la venta
            foreach ($detallesVenta as $detalle) {
                $artId = $detalle['artId'];
                $vndCantidad = $detalle['vndCantidad'];

                // Obtener la cantidad actual del artículo
                $articulo = DB::table('intarticulo')->where('artId', $artId)->first();

                if (!$articulo || $articulo->artCantidad < $vndCantidad) {
                    // No hay suficiente stock para el producto, realizar un rollback
                    DB::rollBack();
                    return response()->json(['error' => 'No hay suficiente stock para el producto:  ' . $articulo->artNombre], 400);
                }
            }

            $tablaVntTxn = 'vntTxn';
            // Insertar datos en la tabla vntTxn usando el controlador VntTxnController
            $vntId = DB::table($tablaVntTxn)->insertGetId([
                'cliId' => $cliId,
                'userId' => $userId,
                'ttxId' => $ttxId,
                'vntNumero' => $nuevoVntNumero,
                'vntCredito' => $vntCredito,
                'fpId' => $fpId,
                'vntActivo' => $vntActivo,
                'vntFechaCreacion' => now(),
            ]);

            // Insertar detalles de los productos vendidos en la tabla vnDetTxn usando el controlador vnDetTxnController
            foreach ($detallesVenta as $detalle) {
                $detalle['vntid'] = $vntId; // Asignar el vntId al detalle para asociarlo a la venta creada
                $vnDetTxn =  $vnDetTxnController->guardarDetalleVenta(new Request($detalle));
            }
            // echo "$vnDetTxn";

            // Insertar movimiento en inventario usando el controlador InvTxnController
            $Modulo = "Venta";
            $invActivo = 1;
            $obj_invTxn->guardarMovimiento(
                now(),
                $ttxId,
                $nuevoVntNumero,
                $Modulo,
                $userId,
                $invActivo,
                now(),
                $detallesVenta
            );
            foreach ($detallesVenta as $detalle) {
                $artId = $detalle['artId'];
                $vndCantidad = $detalle['vndCantidad'];
                $intArticuloController->disminuirCantidadArticulo($artId, $vndCantidad);
            }


        

            // Registrar en la bitácora usando el controlador bitacoraController
            $bitacoraController->insertarBitacora($tablaVntTxn, $vntId, $userId, 'Creación de registro', 'Nueva Venta' . "-" . $nuevoVntNumero);

            DB::commit();
            return response()->json(['mensaje' => 'Venta ' . $nuevoVntNumero . ' registrada con éxito','cjtReferencia' => $nuevoVntNumero], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'No se Logró realizar la operación de la Venta: ' . $e->getMessage()], 409);
        }
    }

    public function InactiveVenta(Request $request )  {
        $obj_bitacora = new bitacoraController();
        $userId = $request->userId;

        db::beginTransaction();
        try {
            $tabla='vnttxn';
            DB::table($tabla)
            ->where('vntId',$request->vntId)
            ->update(['vntActivo' => 0]);   
            
            $obj_bitacora->insertarBitacora($tabla,$request->vntId,$userId,'ANULACION','Elimnacion de Venta');
            $resultRollback = $this->RollbackVenta($request->vntId,$userId);
            DB::commit();
            return response()->json(['Mensaje' => 'Operaciòn Realizado con Éxito'], 201);           
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'No se Logró realizar la operación de la Venta: '. $e->getMessage()], 409); 
        }
    }

    public function RollbackVenta($vntId,$userId) {
        $intArticuloController = new intArticuloController();
        $obj_cjttxn = new cjtTxnController();

        /** 00.Obtenemos el Nro de Venta */
        $vntNumero = DB::table('vntTxn')->where('vntId',$vntId)->first();   
        
        /** 01.Obtenemos el detalle actual de ventas para revertir la cantidad del stock en los articulos */    
        $detalleVenta = DB::table('vndettxn')->where('vntid',$vntId)->where('vndActivo',1)->get();
        foreach ($detalleVenta as $itemDetalle) {
            $artId = $itemDetalle->artId;
            $Cantidad = $itemDetalle->vndCantidad;
            $intArticuloController->aumentarCantidadArticulo($artId, $Cantidad);
        }   
        
        /** 02. Inactivamos el detalle de ventas con 0=inactivo */
        DB::table('vndettxn')->where('vntid', $vntId)->where('vndActivo',1)->update(['vndActivo' => 0]);
        
        /** 03. Obtenemos el Id del inventario de ventas*/ 
        $invId = DB::table('invtxn')->where('invReferencia',$vntNumero->vntNumero)->where('invActivo',1)->first();
        
        /** 03. Inactivamos el movimiento de inventario */
        DB::table('invtxn')->where('invId', $invId->invId)->update(['invActivo' => 0]);
        
        /** 04. Inactivamos el detalle de transacción */
        DB::table('indettxn')->where('invId', $invId->invId)->update(['indActivo' => 0]);
        
        /** 05. Anulamos el movimiento en CAJA */
        $obj_cjttxn->AnularMovimientoCaja($vntNumero->vntNumero, $userId);
        
        
    }   


    
    public function ListaFormaPago()
    {

        try {
            //code..
            $FormaPago = DB::table(('gntformapago as fp'))->select('fp.*')->where('fp.fpActivo', 1)->get();
            return response()->json(($FormaPago));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las ventas realizadas: ' . $e->getMessage()], 500);
        }
    }
    public function obtenerVentasRealizadas()
    {
   
        try {
            $ventas = DB::table('vntTxn')
                ->join('gntcliente', 'vntTxn.cliId', '=', 'gntcliente.cliId')
                ->leftJoin('gntFormaPago', 'vntTxn.fpId', '=', 'gntFormaPago.fpId') // Unir la tabla de forma de pago
                ->select(
                    'vntTxn.vntId',
                    'vntTxn.vntNumero',
                    DB::raw('CONCAT(gntcliente.cliNombre, " ", gntcliente.cliApp, " ", gntcliente.cliApm) AS nombreCliente'),
                    'vntTxn.vntFechaCreacion',
                    DB::raw('SUM(CASE WHEN vnDetTxn.vndDescuento > 0 THEN vnDetTxn.vndDescuento * vnDetTxn.vndCantidad ELSE vnDetTxn.vndPrecioVenta * vnDetTxn.vndCantidad END) as totalVenta'),
                    'gntFormaPago.fpnombre as FormaPago' // Utilizar el nombre de la forma de pago desde la tabla gntFormaPago
                )
                ->leftJoin('vnDetTxn', 'vntTxn.vntId', '=', 'vnDetTxn.vntid')
                ->where('vntTxn.vntCredito', 0) // Filtrar solo las ventas que no son a crédito
                ->where('vntTxn.vntActivo', 1) // Filtrar solo las ventas activas
                ->whereDate('vntTxn.vntFechaCreacion',now()->toDateString())
                ->groupBy('vntTxn.vntId', 'vntTxn.vntNumero', 'gntcliente.cliNombre', 'gntcliente.cliApp', 'gntcliente.cliApm', 'vntTxn.vntFechaCreacion')
                ->orderBy('vntNumero', 'desc')
                ->get();

            return response()->json($ventas);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al obtener las ventas realizadas: ' . $e->getMessage()], 500);
        }
    }


    public function Venta(Request $request)
    {  
      $dia=$request->dia;
        try {
            $ven = DB::table('vntTxn')
                ->join('gntcliente', 'vntTxn.cliId', '=', 'gntcliente.cliId')
                ->leftJoin('gntFormaPago', 'vntTxn.fpId', '=', 'gntFormaPago.fpId') // Unir la tabla de forma de pago
                ->select(
                    'vntTxn.vntId',
                    'vntTxn.vntNumero',
                    DB::raw('CONCAT(gntcliente.cliNombre, " ", gntcliente.cliApp, " ", gntcliente.cliApm) AS nombreCliente'),
                    'vntTxn.vntFechaCreacion',
                    DB::raw('SUM(CASE WHEN vnDetTxn.vndDescuento > 0 THEN vnDetTxn.vndDescuento * vnDetTxn.vndCantidad ELSE vnDetTxn.vndPrecioVenta * vnDetTxn.vndCantidad END) as totalVenta'),
                    'gntFormaPago.fpnombre as FormaPago' // Utilizar el nombre de la forma de pago desde la tabla gntFormaPago
                )
                ->leftJoin('vnDetTxn', 'vntTxn.vntId', '=', 'vnDetTxn.vntid')
                ->where('vntTxn.vntCredito', 0) // Filtrar solo las ventas que no son a crédito
                ->where('vntTxn.vntActivo', 1) // Filtrar solo las ventas activas
                ->whereDate('vntTxn.vntFechaCreacion',$dia)
                ->groupBy('vntTxn.vntId', 'vntTxn.vntNumero', 'gntcliente.cliNombre', 'gntcliente.cliApp', 'gntcliente.cliApm', 'vntTxn.vntFechaCreacion')
                ->orderBy('vntNumero', 'asc')
                ->get();

            return response()->json($ven);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al obtener las ventas realizadas: ' . $e->getMessage()], 500);
        }
    }


  

}
