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
    protected $fillable = ['issuance_id', 'order_id', 'used_at'];

    /**
     * Relationships
     */

    // A redemption belongs to an issuance
    public function issuance()
    {
        return $this->belongsTo(Issuance::class);
    }

    // A redemption belongs to an order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
