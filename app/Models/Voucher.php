<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type_discount',
        'discount_amount',
        'expired_at'
    ];

    public function issuances()
    {
        return $this->hasMany(Issuance::class);
    }
}
