<?php

namespace App\Http\Controllers;

use App\Models\FpDocumentosEntregasModel;
use App\Models\FpProductosPorPedidosModel;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class FpEntregasController extends Controller
{
  public static function setDocumentEntrega(array $pedido)
  {

    try {

      $pedido = array_column($pedido, "id_pedido")[0];

      $productosForDocument = FpProductosPorPedidosModel::join("fp_pedidos", "fp_pedidos.id_pedido", "fp_productos_x_pedido.id_pedido")
        ->where([
          ["fp_productos_x_pedido.id_pedido", $pedido]
        ])->get()->toArray();

      foreach ($productosForDocument as $key => $document) {

        $newDocumento = new FpDocumentosEntregasModel();
        $newDocumento->direccion_entrega = $document["direccion_entrega"];
        $newDocumento->fecha_despacho = $document["fecha_despacho"];
        $newDocumento->id_cliente = $document["id_cliente"];
        $newDocumento->id_linea_producto = $document["id"];
        $newDocumento->save();

        FpProductosPorPedidosModel::where("id", $document["id"])->update([
          "id_documento_entrega" => $newDocumento->id
        ]);
      }

      return response()->json(["status" => 1, "message" => "Documento generado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }
}
