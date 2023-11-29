<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FpGuiasEnvioController;
use App\Http\Controllers\FpProductosController;
use App\Http\Controllers\FpClientesController;
use App\Http\Controllers\FpEntregasController;
use App\Http\Controllers\FpPedidosController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group([
  // 'middleware' => 'jwt.fverify',
  'prefix' => "clientes"
], function ($router) {
  Route::post('updateCliente', [FpClientesController::class, 'updateCliente']);
  Route::post('deleteCliente', [FpClientesController::class, 'deleteCliente']);
});

Route::group([
  // 'middleware' => 'jwt.verify',
  'prefix' => "productos"
], function ($router) {
  Route::post('updateProducto', [FpProductosController::class, 'updateProducto']);
  Route::post('deleteProducto', [FpProductosController::class, 'deleteProducto']);
  Route::get('getProductos', [FpProductosController::class, 'getProductos']);
  Route::post('addProducto', [FpProductosController::class, 'addProducto']);
});

Route::group([
  // 'middleware' => 'jwt.verify',
  'prefix' => "pedidos"
], function ($router) {
  // Route::post('updatePedido', [FpPedidosController::class, 'updatePedido']);
  Route::post('deletePedido', [FpPedidosController::class, 'deletePedido']);
  Route::post('addPedido', [FpPedidosController::class, 'addPedido']);
});

Route::group([
  // 'middleware' => 'jwt.verify',
  'prefix' => "guias"
], function ($router) {
  Route::post('generateGuiasTransporte', [FpGuiasEnvioController::class, 'generateGuiasTransporte']);
  Route::get('getGuiasPerClient/{id_guia}', [FpGuiasEnvioController::class, 'getGuiasPerClient']);
  Route::get('getGuiaPDF/{id_guia}/{id_cliente}', [FpGuiasEnvioController::class, 'getGuiaPDF']);
});

Route::group([
  // 'middleware' => 'jwt.verify',
  'prefix' => "documentos"
], function ($router) {
  Route::get('getDocumentoPDF/{id_guia}/{id_documento}', [FpEntregasController::class, 'getDocumentoPDF']);
  Route::get('getDocumentosPerGuia/{id_guia}', [FpEntregasController::class, 'getDocumentosPerGuia']);
});
