<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FpPedidosModel extends Model
{
  protected $table = "fp_pedidos";
  protected $primaryKey = "id_pedido";
  public $incrementing = true;
  public $timestamps = false;
}
