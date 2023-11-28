<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = ['tracking_number', 'delivery_date'];

    public function proofOfDelivery()
    {
        return $this->hasOne(ProofOfDelivery::class);
    }
}
