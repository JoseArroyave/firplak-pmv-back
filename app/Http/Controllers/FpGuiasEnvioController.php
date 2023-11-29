<?php

namespace App\Http\Controllers;

use App\Models\FpProductosPorPedidosModel;
use App\Models\FpDocumentosEntregasModel;
use Illuminate\Routing\Controller;
use App\Models\FpGuiasEnvioModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FpGuiasEnvioController extends Controller
{
  public static function generateGuiasTransporte()
  {

    try {

      $documentos = FpDocumentosEntregasModel::join("fp_productos_x_pedido", "fp_documentos_entrega.id_linea_producto", "fp_productos_x_pedido.id")
        ->select("fp_documentos_entrega.direccion_entrega", "fp_documentos_entrega.id_cliente", "fp_documentos_entrega.fecha_despacho")
        ->groupBy("fp_documentos_entrega.direccion_entrega", "fp_documentos_entrega.id_cliente", "fp_documentos_entrega.fecha_despacho")
        ->get()
        ->toArray();

      foreach ($documentos as $key => $documento) {

        $toInsert = FpProductosPorPedidosModel::join("fp_pedidos", "fp_productos_x_pedido.id_pedido", "fp_pedidos.id_pedido")
          ->where([
            ["fp_productos_x_pedido.direccion_entrega", $documento["direccion_entrega"]],
            ["fp_productos_x_pedido.fecha_despacho", $documento["fecha_despacho"]],
            ["fp_pedidos.id_cliente", $documento["id_cliente"]],
          ])->pluck("id");

        foreach ($toInsert as $key => $value) {
          if (!FpGuiasEnvioModel::where('id_linea_producto', $value)->exists()) {
            $id_guia = $documento["id_cliente"] . md5($documento["direccion_entrega"]) . strtotime($documento["fecha_despacho"]);
            FpGuiasEnvioModel::insert(["id_linea_producto" => $value, "id_guia" => $id_guia]);
          }
        }
      }

      return response()->json(["status" => 1, "message" => "Guias generadas exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  public function getGuiasPerClient(Request $request)
  {
    try {

      $guias = FpGuiasEnvioModel::join("fp_productos_x_pedido", "fp_guias.id_linea_producto", "fp_productos_x_pedido.id")
        ->join("fp_pedidos", "fp_productos_x_pedido.id_pedido", "fp_pedidos.id_pedido")
        ->join("fp_productos", "fp_productos_x_pedido.SKU", "fp_productos.SKU")
        ->join("fp_clientes", "fp_pedidos.id_cliente", "fp_clientes.id_cliente")
        ->where([
          ["fp_pedidos.id_cliente", $request->id_cliente],
          ["fp_clientes.id_cliente", $request->id_cliente],
        ])
        ->get()
        ->toArray();

      return response()->json(["status" => 1, "message" => $guias]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }
}
