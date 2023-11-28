<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FpDocumentosEntregasModel extends Model
{
  protected $table = "fp_documentos_entrega";
  protected $primaryKey = "id";
  public $incrementing = true;
  public $timestamps = false;
}
