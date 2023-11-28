<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FpOrdenesFabricacionModel extends Model
{
  protected $table = "fp_ordenes_fabricacion";
  protected $primaryKey = "id";
  public $incrementing = true;
  public $timestamps = false;
}
