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

class cmtxnController extends Controller
{
    //Obtner las compras Realizadas 
    public function GetAllShopping()
    {
        try {
            $dataResults = DB::table('cmtxn')
            ->join('gntproveedor', 'cmtxn.provId', '=', 'gntproveedor.provID')
            ->leftJoin('cmdettxn', 'cmtxn.cmtId', '=', 'cmdettxn.cmdtId')
            ->select(
                'cmtxn.cmtId',
                'cmtxn.cmtNumero',
                DB::raw('CONCAT(gntproveedor.provNombre, " ", gntproveedor.provTelefono) AS nombreProveedor'),
                'cmtxn.cmtFechaCreacion',
                DB::raw('SUM(cmdettxn.cmdCantidad * cmdettxn.cmdCosto) as totalCompras')
            )
            ->where('cmtxn.cmtActivo', 1)
            ->groupBy('cmtxn.cmtId', 'cmtxn.cmtNumero', 'gntproveedor.provNombre', 'gntproveedor.provTelefono','cmtxn.cmtFechaCreacion')
            ->orderBy('cmtNumero', 'desc')
            ->get();
            return response()->json($dataResults);
        } catch (\Exception $ex) {
            return response()->json(['mensaje' => 'Error al obtener las Compras Realizadas: ' . $ex->getMessage()], 500);
        }
    }
    public function addShopping(Request $request)
    {
        DB::beginTransaction();
        try {
            $provId = $request->provId;
            $userId = $request->userId;
            $ttxnId = $request->txnId;
            $cmtFechaCompra = $request->cmtFechaCompra;
            $cmtActivo = $request->cmtActivo;

            
            // Verificar si existen compras 
            $cmtNumero = DB::table('cmtxn')->max('cmtNumero');

            if ($cmtNumero === null) {
                // No hay compras, generar el primer número de venta
                $nuevoCmtNumero = 'CM000001';
            } else {
                // Generar el nuevo número de venta en el formato "VE000000"
                $nuevoCmtNumero = 'CM' . str_pad((int)substr($cmtNumero, 2) + 1, 6, '0', STR_PAD_LEFT);
                
            }

            // Obtener detalles de los productos comprados//
            // $detalleCompra = $request->detallesCompra;   //esto se descomenta para probar en postman
            $detalleCompra = json_decode($request->input('detallesCompra'), true);

            // Objetos
            $obj_invTxn = new InvTxnController();
            $cmDetTxnController = new cmDetTxnController();
            $bitacoraController = new bitacoraController();
            $intArticuloController = new intArticuloController();
            $obj_cjttxn = new cjtTxnController();
            // Validar la cantidad disponible antes de guardar la venta
            // foreach ($detalleCompra as $detalle) {
            //     $artId = $detalle['artId'];
            //     $vndCantidad = $detalle['cmdCantidad'];

            //     // Obtener la cantidad actual del artículo
            //     $articulo = DB::table('intarticulo')->where('artId', $artId)->first();

            //     // if (!$articulo || $articulo->artCantidad < $vndCantidad) {
            //     //     // No hay suficiente stock para el producto, realizar un rollback
            //     //     DB::rollBack();
            //     //     return response()->json(['mensaje' => 'No hay suficiente stock para el producto con ID ' . $artId], 400);
            //     // }
            // }

            $tablaCmtTxn = 'cmtxn';
            // Insertar datos en la tabla cmtxn usando el controlador CmtTxnController
            $cmtId = DB::table($tablaCmtTxn)->insertGetId([
                'provId' => $provId,
                'userId' => $userId,
                'ttxnId' => $ttxnId,
                'cmtNumero' => $nuevoCmtNumero,
                'cmtFechaCompra' => $cmtFechaCompra,
                'cmtActivo' => $cmtActivo,
                'cmtFechaCreacion' => now(),
            ]);
            // Insertar detalles de los productos comprados en la tabla cmDetTxn usando el controlador cmDetTxnController
            foreach ($detalleCompra as $detalle) {
                $detalle['cmtId'] = $cmtId; // Asignar el cmtId al detalle para asociarlo a la compra realizada
                $detalle['cmtFechaCompra']=$cmtFechaCompra;
                $cmDetTxn =  $cmDetTxnController->AddDetailShopping(new Request($detalle));
            }
            // Insertar movimiento en inventario usando el controlador InvTxnController
            $Modulo = "Compras";
            $invActivo = 1;
            $obj_invTxn->guardarMovimiento(
                now(),
                $ttxnId,
                $nuevoCmtNumero,
                $Modulo,
                $userId,
                $invActivo,
                now(),
                $detalleCompra
            );
            foreach ($detalleCompra as $detalle ) {
                $artId= $detalle['artId'];
                $cmdCantidad= $detalle['cmdCantidad'];
                $intArticuloController->aumentarCantidadArticulo($artId,$cmdCantidad);
            }
            
            // Registrar en la bitácora usando el controlador bitacoraController
            $bitacoraController->insertarBitacora($tablaCmtTxn, $cmtId, $userId, 'Creación de registro', 'Nueva Compra' . "-" . $nuevoCmtNumero);

            DB::commit();
            return response()->json(['mensaje' => 'Compra ' . $nuevoCmtNumero . ' registrada con éxito','cjtReferencia' => $nuevoCmtNumero], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'No se Logró realizar la operación de la Compra: ' . $e->getMessage()], 409);
        }
    }
}
