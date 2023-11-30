<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\FpProductosModel;
use Illuminate\Http\Request;

class FPProductosController extends Controller
{

  /**
   * La función `addProducto` agrega un nuevo producto a la base de datos y devuelve una respuesta JSON
   * indicando el éxito o fracaso de la operación.
   */
  public function addProducto(Request $request)
  {
    try {

      $pedido = new FpProductosModel();
      $pedido->tipo_producto = $request->tipo_producto;
      $pedido->descripcion = $request->descripcion;
      $pedido->precio = $request->precio;
      $pedido->save();

      return response()->json(["status" => 1, "message" => "Producto agregado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  /**
   * La función `updateProducto` actualiza la descripción y el precio de un producto en la tabla
   * FpProductosModel según el SKU proporcionado en la solicitud y devuelve una respuesta JSON indicando
   * el éxito o fracaso de la actualización.
   */
  public function updateProducto(Request $request)
  {
    try {

      FpProductosModel::where("SKU", $request->SKU)->update([
        "descripcion" => $request->descripcion,
        "precio" => $request->precio,
      ]);

      return response()->json(["status" => 1, "message" => "Producto actualizado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  /**
   * La función elimina un producto de la base de datos según el SKU proporcionado en la solicitud.
   */
  public function deleteProducto(Request $request)
  {
    try {

      FpProductosModel::where("SKU", $request->SKU)->delete();

      return response()->json(["status" => 1, "message" => "Producto eliminado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  /**
   * La función `getProductos` devuelve una respuesta JSON que contiene el estado y el mensaje, que
   * incluye el resultado del método `FpProductosModel::get()`, o un mensaje de error con la línea y el
   * archivo donde ocurrió el error.
   */
  public function getProductos()
  {
    try {
      return response()->json(["status" => 1, "message" => FpProductosModel::get()]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }
}
