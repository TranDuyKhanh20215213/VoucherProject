<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    public $timestamps = false;
    protected $fillable = ['product_id', 'issuance_id', 'payment_method', 'used_at'];

    /**
     * Relationships
     */

    // A redemption belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // A redemption belongs to an issuance
    public function issuance()
    {
        return $this->belongsTo(Issuance::class);
    }
}
