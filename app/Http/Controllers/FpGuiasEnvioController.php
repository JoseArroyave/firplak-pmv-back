<?php

namespace App\Http\Controllers;

use App\Models\FpProductosPorPedidosModel;
use App\Models\FpDocumentosEntregasModel;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\FpGuiasEnvioModel;

class FpGuiasEnvioController extends Controller
{
  public function generateGuiasTransporte()
  {

    DB::beginTransaction();
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

      DB::commit();
      return response()->json(["status" => 1, "message" => "Guias generadas exitosamente"]);
    } catch (\Throwable $th) {
      DB::rollBack();
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }
}
