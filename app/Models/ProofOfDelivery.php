<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfDelivery extends Model
{
    protected $fillable = ['customer_signature', 'delivery_photo_path'];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
