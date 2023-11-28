<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\FpProductosModel;
use Illuminate\Http\Request;

class FPProductosController extends Controller
{
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

  public function deleteProducto(Request $request)
  {
    try {

      FpProductosModel::where("SKU", $request->SKU)->delete();

      return response()->json(["status" => 1, "message" => "Producto eliminado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }
}
