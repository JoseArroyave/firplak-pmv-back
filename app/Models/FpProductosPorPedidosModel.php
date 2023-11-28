<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FpProductosPorPedidosModel extends Model
{
  protected $table = "fp_productos_x_pedido";
  protected $primaryKey = "id";
  public $incrementing = true;
  public $timestamps = false;
}
