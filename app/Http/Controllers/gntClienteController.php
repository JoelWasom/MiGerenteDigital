<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class gntClienteController extends Controller
{
    //
    public function ListaCliente()
    {

        try {
            //code..
            $listaCliente = DB::table(('gntcliente as cli'))
            ->select('cli.*')
            ->where('cli.cliActivo', 1)->get();
            return response()->json(($listaCliente));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las ventas realizadas: ' . $e->getMessage()], 500);
        }
    }
}
