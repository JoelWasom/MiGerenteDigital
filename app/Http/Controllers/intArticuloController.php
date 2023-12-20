<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Controllers\bitacoraController;
// use Intervention\Image\ImageManagerStatic as Image;

class intArticuloController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //intartuculo


    public function listArticulo()
    {
        // $consulta = "SELECT * FROM vi_lista_articulo";
        // $query = DB::select($consulta);
        // return response()->json($query, 200);
        $articulo = DB::table('vi_lista_articulo')->get();

        if ($articulo->isNotEmpty()) {
            $articuloUtf8 = $articulo->map(function ($item) {
                $item->artFoto = base64_encode($item->artFoto);
                return $item;
            });
            return response()->json($articulo);
        } else {
            return response()->json(['Mensaje' => 'No se encontraron datos']);
        }
    }
    public function siguienteCodigo()
    {
        $consulta = "SELECT MAX(CAST(REGEXP_REPLACE(intarticulo.artCodigo, '[^0-9]', '') AS UNSIGNED)) + 1 AS siguiente
        FROM intarticulo";

        $query = DB::select($consulta);
        return response()->json($query, 200);
    }

    public function CantidadCategoria(Request $request)
    {

        $resultados = DB::table('intarticulo')
            ->join('intcategoria', 'intarticulo.catId', '=', 'intcategoria.catId')
            ->select('intcategoria.catId', 'intcategoria.catNombre', DB::raw('COUNT(*) AS cantidad_productos'))
            ->where('intcategoria.catId', $request->txt_catId)
            ->groupBy('intcategoria.catId', 'intcategoria.catNombre')
            ->orderBy('cantidad_productos', 'asc')
            ->get();

        return response()->json($resultados, 200);
    }
    public function CantidadCategoria_todo()
    {

        $resultados = DB::table('intarticulo')
            ->join('intcategoria', 'intarticulo.catId', '=', 'intcategoria.catId')
            ->select('intcategoria.catId', 'intcategoria.catNombre', DB::raw('COUNT(*) AS cantidad_productos'))
            ->groupBy('intcategoria.catId', 'intcategoria.catNombre')
            ->orderBy('cantidad_productos', 'desc')
            ->get();

        return response()->json($resultados, 200);
    }
    public function cbxCategoria()
    {
        $consulta = "SELECT * FROM intcategoria  WHERE intcategoria.catActivo=1";
        $query = DB::select($consulta);
        return response()->json($query, 200);
    }



    public function cbxMarca()
    {
        $consulta = "SELECT * FROM intmarca  WHERE intmarca.marActivo=1";
        $query = DB::select($consulta);
        return response()->json($query, 200);
    }
    public function cbxUnidad()
    {
        $consulta = "SELECT * FROM intunidad  WHERE intunidad.uniActivo=1";
        $query = DB::select($consulta);
        return response()->json($query, 200);
    }

    public function guardarArticulo(Request $request)
    {
        $obj_bitacora = new bitacoraController();
        $artCodigo = strtoupper($request->txt_artCodigo);
        $UsuarioId = $request->UsuarioId;

        $existeArticulo = DB::table('intarticulo')
            ->where('artCodigo', $artCodigo)
            ->exists();

        if ($existeArticulo) {
            return response()->json(['Mensaje' => 'El código de artículo ya existe'], 409);
        }

        DB::beginTransaction();
        try {
            $tabla = 'intarticulo';
            $artFoto = null;
            if ($request->hasFile('artFoto')) {
                $artFoto = $request->file('artFoto')->get();
            }
            DB::table($tabla)->insert([
                'artCodigo' => $artCodigo,
                'artCodigoBarra' => $request->txt_artCodigoBarra,
                'artNombre' => strtoupper($request->txt_artNombre),
                'catId' => $request->cbx_catId,
                'marId' => $request->cbx_marId,
                'uniId' => $request->cbx_uniId,
                'artCosto' => $request->txt_artCosto,
                'artPrecioVenta' => $request->txt_artPrecioVenta,
                'artCantidad' => $request->txt_artCantidad,
                'artFoto' => $artFoto,
                'artCantMin' => $request->txtCantMin,
                'artActivo' => $request->artActivo,
                'artFechaCreacion' => $request->artFechaCreacion,
            ]);
            $obj_bitacora->insertarBitacora($tabla, $artCodigo, $UsuarioId, 'Creación de registro', 'Nuevo Registro de Articulo');
            DB::commit();
            return response()->json(['Mensaje' => 'Realizado con Éxito'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['Mensaje' => 'No se Logró Realizar la Operación: ' . $e->getMessage()], 409);
        }
    }
    public function modificarArticulo(Request $request)
    {
        $obj_bitacora = new bitacoraController();
        $artCodigo = strtoupper($request->txt_artCodigo);
        $UsuarioId = $request->UsuarioId;
        DB::beginTransaction();
        try {
            $tabla = 'intarticulo';
            $artFoto = null;
            if ($request->hasFile('artFoto')) {
                $artFoto = $request->file('artFoto')->get();
                DB::table($tabla)
                    ->where('artId', $request->artId)
                    ->update([
                        'artCodigo' => $artCodigo,
                        'artCodigoBarra' => $request->txt_artCodigoBarra,
                        'artNombre' => strtoupper($request->txt_artNombre),
                        'catId' => $request->cbx_catId,
                        'marId' => $request->cbx_marId,
                        'uniId' => $request->cbx_uniId,
                        'artCosto' => $request->txt_artCosto,
                        'artPrecioVenta' => $request->txt_artPrecioVenta,
                        'artCantidad' => $request->txt_artCantidad,
                        'artCantMin' => $request->txtCantMin,
                        'artFoto' => $artFoto,
                        'artActivo' => $request->artActivo,
                        'artFechaCreacion' => $request->artFechaCreacion,
                    ]);
            } else {
                DB::table($tabla)
                    ->where('artId', $request->artId)
                    ->update([
                        'artCodigo' => $artCodigo,
                        'artCodigoBarra' => $request->txt_artCodigoBarra,
                        'artNombre' => strtoupper($request->txt_artNombre),
                        'catId' => $request->cbx_catId,
                        'marId' => $request->cbx_marId,
                        'uniId' => $request->cbx_uniId,
                        'artCosto' => $request->txt_artCosto,
                        'artPrecioVenta' => $request->txt_artPrecioVenta,
                        'artCantidad' => $request->txt_artCantidad,
                        'artCantMin' => $request->txtCantMin,
                        'artActivo' => $request->artActivo,
                        'artFechaCreacion' => $request->artFechaCreacion,
                    ]);
            }

            $obj_bitacora->insertarBitacora($tabla, $artCodigo, $UsuarioId, 'Modificaciòn de registro', 'Modificaciòn de Articulo');
            DB::commit();
            return response()->json(['Mensaje' => 'Realizado con Éxito'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['Mensaje' => 'No se Logró Realizar la Operación: ' . $e->getMessage()], 409);
        }
    }
    public function eliminarArticulo(Request $request)
    {
        $obj_bitacora = new bitacoraController();
        $artCodigo = strtoupper($request->txt_artCodigo);
        $UsuarioId = $request->UsuarioId;
        DB::beginTransaction();
        try {
            $tabla = 'intarticulo';
            DB::table($tabla)
                ->where('artId', $request->artId)
                ->update([
                    'artActivo' => 0,
                ]);
            $obj_bitacora->insertarBitacora($tabla, $artCodigo, $UsuarioId, 'Eliminación de registro', 'Eliminación de Articulo');
            DB::commit();
            return response()->json(['Mensaje' => 'Realizado con Éxito'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['Mensaje' => 'No se Logró Realizar la Operación: ' . $e->getMessage()], 409);
        }
    }

    public function TraeArticulo(Request $request)
    {
        $articulo = DB::table('intarticulo')
            ->join('intcategoria', 'intarticulo.catId', '=', 'intcategoria.catId')
            ->join('intmarca', 'intarticulo.marId', '=', 'intmarca.marId')
            ->join('intunidad', 'intarticulo.uniId', '=', 'intunidad.uniId')
            ->where('intarticulo.artId', '=', $request->artId)
            ->select('intarticulo.*', 'intcategoria.*', 'intmarca.*', 'intunidad.*')
            ->get();

        if ($articulo->isNotEmpty()) {
            $articuloUtf8 = $articulo->map(function ($item) {
                $item->artFoto = base64_encode($item->artFoto);
                return $item;
            });
            return response()->json($articulo);
        } else {
            return response()->json(['Mensaje' => 'No se encontraron datos'], 404);
        }
    }



    public function buscarPorNombre(Request $request)
    {
        try {
            // Realizar la consulta para obtener todos los artículos sin filtrar
            $articulo = DB::table('intarticulo')
                ->where('artActivo', 1) // Filtrar por artículos activos (opcional, si deseas solo los activos)
                ->get();

            if ($articulo->isNotEmpty()) {
                $articuloUtf8 = $articulo->map(function ($item) {
                    $item->artFoto = base64_encode($item->artFoto);
                    return $item;
                });
                return response()->json($articulo);
            } else {
                return response()->json(['Mensaje' => 'No se encontraron datos'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['Mensaje' => 'Error al obtener los artículos: ' . $e->getMessage()], 500);
        }
    }
    public function disminuirCantidadArticulo($artId, $cantidad)
    {
        DB::beginTransaction();
        try {
            // Obtén la cantidad actual del artículo
            $articulo = DB::table('intarticulo')->where('artId', $artId)->first();

            // Verifica si el artículo existe y si la cantidad actual es suficiente para la venta
            if ($articulo && $articulo->artCantidad >= $cantidad) {
                // Calcula la nueva cantidad restando la cantidad vendida
                $nuevaCantidad = $articulo->artCantidad - $cantidad;
                // Actualiza la cantidad en la base de datos
                DB::table('intarticulo')->where('artId', $artId)->update(['artCantidad' => $nuevaCantidad]);
                DB::commit(); // Confirma la transacción si todo está bien
            } else {
                DB::rollBack(); // Revierte la transacción si el artículo no existe o la cantidad es insuficiente
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Revierte la transacción en caso de error
            throw $e;
        }
    }


    public function aumentarCantidadArticulo($artId, $cantidad)
    {
        DB::beginTransaction();
        try {
            // Obtén la cantidad actual del artículo
            $articulo = DB::table('intarticulo')->where('artId', $artId)->first();

            // Verifica si el artículo existe
            if ($articulo) {
                // Calcula la nueva cantidad sumando la cantidad especificada
                $nuevaCantidad = $articulo->artCantidad + $cantidad;
                // Actualiza la cantidad en la base de datos
                DB::table('intarticulo')->where('artId', $artId)->update(['artCantidad' => $nuevaCantidad]);
                DB::commit(); // Confirma la transacción si todo está bien
            } else {
                DB::rollBack(); // Revierte la transacción si el artículo no existe
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Revierte la transacción en caso de error
            throw $e;
        }
    }
}
