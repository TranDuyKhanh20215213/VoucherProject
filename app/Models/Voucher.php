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
        'expired_at',
        'rule' // Adjusted to match the correct field name
    ];

    protected $casts = [
        'rule' => 'array', // Cast the JSON 'rule' column to an array
        'created' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function issuances()
    {
        return $this->hasMany(Issuance::class);
    }

    public function usableProduct($productId)
    {
        // Decode the JSON rule to get an array of eligible product IDs
        $rules = json_decode($this->rule, true);

        // Check if the product ID is in the rules array
        return in_array($productId, $rules);
    }
}
