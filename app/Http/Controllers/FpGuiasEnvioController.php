<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\FpProductosPorPedidosModel;
use App\Models\FpDocumentosEntregasModel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Routing\Controller;
use App\Models\FpGuiasEnvioModel;
use Illuminate\Http\Request;

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

  /**
   * La función `getGuiasPerClient` recupera una lista de guías (guías de envío) para un cliente
   * específico en una aplicación PHP.
   * 
   * @param id_cliente El parámetro "id_cliente" es el ID del cliente del cual se desea recuperar las
   * guías. Se utiliza para filtrar los resultados y devolver solo las guías asociadas a ese cliente.
   * @param notApi El parámetro "notApi" es un indicador booleano que determina si la función debe
   * devolver el resultado como una matriz o como una respuesta JSON.
   * 
   * @return array sea una matriz de "guías" (si  es verdadero) o una respuesta JSON que contiene el
   * estado y el mensaje (si  es falso).
   */
  public function getGuiasPerClient($id_cliente, $notApi = false)
  {
    try {

      $guias = FpGuiasEnvioModel::join("fp_productos_x_pedido", "fp_guias.id_linea_producto", "fp_productos_x_pedido.id")
        ->join("fp_pedidos", "fp_productos_x_pedido.id_pedido", "fp_pedidos.id_pedido")
        ->join("fp_productos", "fp_productos_x_pedido.SKU", "fp_productos.SKU")
        ->join("fp_clientes", "fp_pedidos.id_cliente", "fp_clientes.id_cliente")
        ->select([
          "fp_guias.id_guia",
          "fp_clientes.nombre",
          "fp_clientes.apellido",
          "fp_clientes.id_cliente",
          "fp_guias.pod_registered",
          "fp_productos_x_pedido.fecha_entrega",
          "fp_productos_x_pedido.fecha_despacho",
          "fp_productos_x_pedido.direccion_entrega",
        ])
        ->where([
          ["fp_pedidos.id_cliente", $id_cliente],
          ["fp_clientes.id_cliente", $id_cliente],
        ])
        ->groupBy('fp_guias.id_guia', 'fp_clientes.nombre', 'fp_clientes.apellido', 'fp_clientes.id_cliente', 'fp_productos_x_pedido.fecha_entrega', 'fp_productos_x_pedido.fecha_despacho', 'fp_productos_x_pedido.direccion_entrega', "fp_guias.pod_registered")
        ->get()
        ->toArray();

      if ($notApi) {
        return $guias;
      }

      return response()->json(["status" => 1, "message" => $guias]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  /**
   * La función `getGuiaPDF` genera un documento PDF que contiene información sobre una guía específica y
   * lo transmite al usuario.
   * 
   * @param id_guia El parámetro "id_guia" representa el ID de una guía específica que desea recuperar.
   * @param id_cliente El parámetro "id_cliente" representa el ID del cliente para quien se genera la
   * guía. Se utiliza para recuperar la información necesaria relacionada con la guía del cliente.
   * 
   * @return blob de un archivo PDF.
   */
  public function getGuiaPDF($id_guia, $id_cliente)
  {
    try {

      $guia = $this->getGuiasPerClient($id_cliente, true);

      $guia = array_values(array_filter($guia, function ($gui) use ($id_guia) {
        return $gui["id_guia"] == $id_guia;
      }))[0];

      $qrcode = base64_encode(QrCode::generate(env("URL_FRONT") . "/POD/$id_guia}"));

      $data = [
        "direccion_entrega" => $guia["direccion_entrega"],
        "fecha_despacho" => $guia["fecha_despacho"],
        "fecha_entrega" => $guia["fecha_entrega"],
        "id_cliente" => $guia["id_cliente"],
        "apellido" => $guia["apellido"],
        "id_guia" => $guia["id_guia"],
        "nombre" => $guia["nombre"],
        "qr" => $qrcode
      ];

      return PDF::loadView('guia', $data)->stream("Guia {$guia["id_guia"]}");
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  /**
   * La función `savePOD` actualiza el campo `pod_registered` en la tabla `FpGuiasEnvioModel` con un
   * valor de 1 para un `id_guia` específico proporcionado en la solicitud.
   */
  public function savePOD(Request $request)
  {
    try {
      FpGuiasEnvioModel::where("id_guia", $request->guia)->update(["pod_registered" => 1]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }
}
