<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'description',
        'type_discount',
        'discount_amount',
        'created',
        'expired_at'
    ];

    public function issuances()
    {
        return $this->hasMany(Issuance::class);
    }

    public function eligibilities()
    {
        return $this->hasMany(Eligibility::class);
    }
}
