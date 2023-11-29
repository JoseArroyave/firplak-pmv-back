<?php

namespace App\Http\Controllers;

use App\Http\Controllers\FpEntregasController;
use App\Models\FpClientesModel;
use App\Models\FpProductosPorPedidosModel;
use App\Models\FpOrdenesFabricacionModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use App\Models\FpProductosModel;
use App\Models\FpPedidosModel;
use Illuminate\Http\Request;
use App\Models\FpPT01Model;
use App\Models\FpPT02Model;

class FpPedidosController extends Controller
{
  public function addPedido(Request $request)
  {

    DB::beginTransaction();

    try {
      $date = now();

      if (!FpClientesModel::where("id_cliente", $request->id_cliente)->exists()) {
        $addCliente = new FpClientesController();
        $addCliente = $addCliente->addCliente($request);
      }

      $pedido = new FpPedidosModel();
      $pedido->id_cliente = $request->id_cliente;
      $pedido->fecha_creacion = $date;
      $pedido->save();

      $allProductos = [];

      foreach ($request->productos as $key => $producto) {
        if ($producto["direccion_entrega"] != null && $producto["cantidad"] != null && $producto["SKU"] != null) {
          $allProductos[] = [
            "direccion_entrega" => $producto["direccion_entrega"],
            "cantidad" => $producto["cantidad"],
            "id_pedido" => $pedido->id_pedido,
            "SKU" => $producto["SKU"],
          ];
        }
      }

      FpProductosPorPedidosModel::insert($allProductos);

      $this->processTipo1Products($pedido, $allProductos);
      $this->processTipo2Products($pedido, $allProductos);

      $setDocumentEntrega = FpEntregasController::setDocumentEntrega($allProductos);
      DB::commit();

      return $setDocumentEntrega;
    } catch (\Throwable $th) {
      DB::rollBack();
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  private function processTipo1Products($pedido, $allProductos)
  {
    $productosBySKU = FpProductosModel::whereIn("SKU", array_column($allProductos, 'SKU'))->get()->toArray();

    $productos_tipo_1 = array_filter($productosBySKU, function ($tipo) {
      return $tipo["tipo_producto"] == 1;
    });

    foreach ($productos_tipo_1 as $each1) {
      $productos = array_values(array_filter($allProductos, function ($producto) use ($each1) {
        return $producto["SKU"] == $each1["SKU"];
      }));

      if (count($productos) > 0) {
        $dias_despacho = +$each1["dias_fabricacion"] + 1;
        $dias_entrega = $dias_despacho + 2;

        FpProductosPorPedidosModel::where("id_pedido", $pedido->id_pedido)->update([
          "fecha_despacho" => now()->addDays($dias_despacho)->setHour(9)->setMinute(0)->setSecond(0),
          "fecha_entrega" => now()->addDays($dias_entrega)->setHour(18)->setMinute(0)->setSecond(0),
        ]);

        FpOrdenesFabricacionModel::insert([
          [
            "fecha_finalizacion" => now()->addDays(3)->setHour(18)->setMinute(0)->setSecond(0),
            "id_pedido" => $pedido->id_pedido,
            "SKU" => $productos[0]["SKU"],
          ],
        ]);

        FpPT02Model::where("SKU", $each1["SKU"])->increment("cantidad", $productos[0]["cantidad"]);
      }
    }
  }

  private function processTipo2Products($pedido, $allProductos)
  {
    $productosBySKU = FpProductosModel::whereIn("SKU", array_column($allProductos, 'SKU'))->get()->toArray();

    $productos_tipo_2 = array_filter($productosBySKU, function ($tipo) {
      return $tipo["tipo_producto"] == 2;
    });

    foreach ($productos_tipo_2 as $each2) {
      $productos = array_values(array_filter($allProductos, function ($producto) use ($each2) {
        return $producto["SKU"] == $each2["SKU"];
      }));

      if (count($productos) > 0) {
        FpProductosPorPedidosModel::where([["id_pedido", $pedido->id_pedido], ["SKU", $productos[0]["SKU"]]])->update([
          "estado" => 1,
          "fecha_despacho" => now()->setHour(18)->setMinute(0)->setSecond(0),
          "fecha_entrega" => now()->addDays(2)->setHour(18)->setMinute(0)->setSecond(0),
        ]);

        FpPT01Model::where("SKU", $each2["SKU"])->decrement("cantidad", $productos[0]["cantidad"]);
      }
    }
  }

  public function deletePedido(Request $request)
  {
    try {
      FpPedidosModel::where("id_pedido", $request->id_pedido)->delete();

      return response()->json(["status" => 1, "message" => "Pedido eliminado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }
}
