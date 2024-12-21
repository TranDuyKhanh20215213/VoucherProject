<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    public $timestamps = false;
    protected $fillable = ['payment_method', 'ordered_at'];

    /**
     * Relationships
     */

    // An order can have many products through the order_product pivot table
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')
            ->withPivot('quantity') ;// Include the quantity from the pivot table
    }

    // An order can have many redemptions
    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }
}
