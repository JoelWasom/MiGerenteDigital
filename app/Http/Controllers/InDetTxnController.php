<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Controllers\bitacoraController;
use App\Http\Controllers\vnDetTxnController;

class InDetTxnController extends Controller
{
    //
    public function guardarDetalleTransaccion($detalle) {


        DB::beginTransaction();
        try {
            // Insertar datos en la tabla inDetTxn
            $tablaInDetTxn = 'inDetTxn';
            DB::table($tablaInDetTxn)->insert([
                'invId' => $detalle['invId'],
                'artId' => $detalle['artId'],
                'indCantidad' => $detalle['vndCantidad'],
                'indPrecio' =>  $detalle['vndPrecioVenta'],
                'indActivo' =>  $detalle['vndActivo'],
                'indFechaCreacion' => now()
            ]);

            DB::commit();
            return response()->json(['Mensaje' => 'Detalle de transacción registrado con éxito'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['Mensaje' => 'No se logró realizar la operación de creacion de detalle: ' . $e->getMessage()], 409);
        }
    }
}
