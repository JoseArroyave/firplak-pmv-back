<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FpProductosModel extends Model
{
  protected $table = "fp_productos";
  protected $primaryKey = "SKU";
  public $incrementing = true;
  public $timestamps = false;
}
