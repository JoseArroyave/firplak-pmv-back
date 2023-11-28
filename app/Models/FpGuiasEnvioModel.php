<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FpGuiasEnvioModel extends Model
{
  protected $table = "fp_guias";
  protected $primaryKey = "id";
  public $incrementing = true;
  public $timestamps = false;
}
