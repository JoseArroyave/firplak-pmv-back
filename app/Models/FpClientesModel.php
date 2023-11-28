<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FpClientesModel extends Model
{
  protected $table = "fp_clientes";
  protected $primaryKey = "id_cliente";
  public $incrementing = false;
  public $timestamps = false;
}
