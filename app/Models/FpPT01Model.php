<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FpPT01Model extends Model
{
  protected $table = "fp_pt01";
  protected $primaryKey = "SKU";
  public $incrementing = false;
  public $timestamps = false;
}
