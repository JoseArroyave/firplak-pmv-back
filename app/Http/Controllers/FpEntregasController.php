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
  /**
   * La función `setDocumentEntrega` crea documentos de entrega para un pedido determinado y actualiza el
   * pedido con el ID del documento correspondiente.
   * 
   * @param array pedido El parámetro es un array que contiene información sobre un pedido.
   */
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

  /**
   * La función "getDocumentosPerGuia" recupera una lista de documentos por guía, según el ID de la guía
   * proporcionado.
   * 
   * @param id_guia El parámetro "id_guia" es el ID de una guía que se utiliza para recuperar los
   * documentos asociados.
   * @param notApi Un parámetro booleano que indica si la función debe devolver el resultado como
   * respuesta API o no. Si se establece en verdadero, la función devolverá el resultado como una matriz.
   * Si se establece en falso (predeterminado), la función devolverá el resultado como una respuesta
   * JSON.
   */
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

  /**
   * La función `getDocumentoPDF` genera un documento PDF basado en los parámetros proporcionados y lo
   * devuelve como una secuencia.
   * 
   * @param id_guia El parámetro "id_guia" representa el ID de una guía o envío. Se utiliza para
   * recuperar los documentos asociados a esa guía.
   * @param id_documento El parámetro "id_documento" es el ID del documento específico que desea
   * recuperar. Se utiliza para filtrar los documentos y encontrar aquel que tenga una identificación
   * coincidente.
   * 
   * @return blob de un archivo PDF.
   */
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
