<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\FpClientesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FpClientesController extends Controller
{
  public function addCliente(Request $request)
  {
    try {

      $pedido = new FpClientesModel();
      $pedido->id_cliente = $request->id_cliente;
      $pedido->nombre = $request->nombre;
      $pedido->apellido = $request->apellido;
      $pedido->save();

      return response()->json(["status" => 1, "message" => "Cliente agregado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  public function updateCliente(Request $request)
  {
    try {

      FpClientesModel::where("id_cliente", $request->id_cliente)->update([
        "apellido" => $request->apellido,
        "nombre" => $request->nombre,
      ]);

      return response()->json(["status" => 1, "message" => "Cliente actualizado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }

  public function deleteCliente(Request $request)
  {
    try {

      FpClientesModel::where("id_cliente", $request->id_cliente)->delete();

      return response()->json(["status" => 1, "message" => "Cliente eliminado exitosamente"]);
    } catch (\Throwable $th) {
      return response()->json(["Error" => $th->getMessage(), "Línea" => $th->getLine(), "Archivo" => __FILE__]);
    }
  }
}
