<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\FpProductosPorPedidosModel;
use App\Models\FpDocumentosEntregasModel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Routing\Controller;
use App\Models\FpGuiasEnvioModel;

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

      return FpGuiasEnvioController::generateGuiasTransporte();
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  public function getDocumentosPerGuia($id_guia, $notApi = false)
  {
    try {

      $idLineaProductoPerGuia = FpGuiasEnvioModel::where("id_guia", $id_guia)->pluck("id_linea_producto");
      $idLineaProductoPerGuia = FpProductosPorPedidosModel::join("fp_productos", "fp_productos_x_pedido.SKU", "fp_productos.SKU")
        ->whereIn("fp_productos_x_pedido.id", $idLineaProductoPerGuia)->get();

      if ($notApi) {
        return $idLineaProductoPerGuia->toArray();
      }

      return response()->json(["status" => 1, "message" => $idLineaProductoPerGuia]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  public function getDocumentoPDF($id_guia, $id_documento)
  {
    try {


      $documento = $this->getDocumentosPerGuia($id_guia, true);

      $documento = array_filter($documento, function ($doc) use ($id_documento) {
        return $doc["id_documento_entrega"] == $id_documento;
      })[0];

      $qrcode = base64_encode(QrCode::generate(env("URL_FRONT") . "/POD/$id_guia}"));

      $data = [
        // "id_documento_entrega" => $documento["id_documento_entrega"],
        "total" => +$documento["precio"] * +$documento["cantidad"],
        // "direccion_entrega" => $documento["direccion_entrega"],
        "dias_fabricacion" => $documento["dias_fabricacion"],
        // "fecha_despacho" => $documento["fecha_despacho"],
        // "fecha_entrega" => $documento["fecha_entrega"],
        // "tipo_producto" => $documento["tipo_producto"],
        "descripcion" => $documento["descripcion"],
        "id_pedido" => $documento["id_pedido"],
        "cantidad" => $documento["cantidad"],
        // "estado" => $documento["estado"],
        "precio" => $documento["precio"],
        "SKU" => $documento["SKU"],
        // "id" => $documento["id"],
        "qr" => $qrcode
      ];

      return PDF::loadView('documento', $data)->stream("Documento {$documento["id_documento_entrega"]}");
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }
}
