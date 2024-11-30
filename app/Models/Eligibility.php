<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eligibility extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    public $timestamps = false;
    protected $fillable = ['voucher_id', 'product_id'];

    /**
     * Relationships
     */

    // An eligibility belongs to a voucher
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    // An eligibility belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
