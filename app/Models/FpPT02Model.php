<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FpPT02Model extends Model
{
  protected $table = "fp_pt02";
  protected $primaryKey = "SKU";
  public $incrementing = false;
  public $timestamps = false;
}
