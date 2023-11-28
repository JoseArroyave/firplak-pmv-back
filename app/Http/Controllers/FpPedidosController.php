<?php

namespace App\Http\Controllers;

use App\Models\FpOrdenesFabricacionModel;
use App\Models\FpProductosPorPedidosModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use App\Models\FpProductosModel;
use App\Models\FpPedidosModel;
use App\Models\FpPT01Model;
use App\Models\FpPT02Model;
use Illuminate\Http\Request;

class FpPedidosController extends Controller
{
  public function addPedido(Request $request)
  {

    DB::beginTransaction();
    try {

      $date = date('Y-m-d H:i:s');

      $pedido = new FpPedidosModel();
      $pedido->id_cliente = $request->id_cliente;
      $pedido->fecha_creacion = $date;
      $pedido->save();

      $allProductos = [];

      foreach ($request->productos as $key => $producto) {
        array_push($allProductos, [
          "fecha_entrega" => date('Y-m-d', strtotime($date . ' +15 days')) . ' 18:00:00',
          "cantidad" => $producto["cantidad"],
          "id_pedido" => $pedido->id_pedido,
          "SKU" => $producto["SKU"],
        ]);
      }

      FpProductosPorPedidosModel::insert($allProductos);

      $productosBySKU = FpProductosModel::whereIn("SKU", array_column($allProductos, 'SKU'))->get()->toArray();

      $productos_tipo_1 = array_column(array_values(array_filter($productosBySKU, function ($tipo) {
        return $tipo["tipo_producto"] == 1;
      })), "SKU");

      $productos_tipo_2 = array_column(array_values(array_filter($productosBySKU, function ($tipo) {
        return $tipo["tipo_producto"] == 2;
      })), "SKU");

      foreach ($productos_tipo_1 as $key => $each1) {
        $productos = array_values(array_filter($allProductos, function ($producto) use ($each1) {
          return $producto["SKU"] == $each1;
        }));

        if (count($productos) > 0) {
          FpPT02Model::where("SKU", $each1)->increment("cantidad", $productos[0]["cantidad"]);

          FpOrdenesFabricacionModel::insert([
            [
              "fecha_finalizacion" => date('Y-m-d', strtotime($date . ' +5 days')) . ' 10:00:00',
              "id_pedido" => $pedido->id_pedido,
              "SKU" => $productos[0]["SKU"],
              "fecha_creacion" => $date,
            ]
          ]);
        }
      }

      foreach ($productos_tipo_2 as $key => $each2) {
        $productos = array_values(array_filter($allProductos, function ($producto) use ($each2) {
          return $producto["SKU"] == $each2;
        }));

        if (count($productos) > 0) {
          FpPT01Model::where("SKU", $each2)->decrement("cantidad", $productos[0]["cantidad"]);
        }
      }

      DB::commit();
      return response()->json(["status" => 1, "message" => "Pedido agendado exitosamente"]);
    } catch (\Throwable $th) {
      DB::rollBack();
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine()]);
    }
  }

  public function deletePedido(Request $request)
  {
    try {

      FpPedidosModel::where("id_pedido", $request->id_pedido)->delete();

      return response()->json(["status" => 1, "message" => "Pedido eliminado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine()]);
    }
  }
}
