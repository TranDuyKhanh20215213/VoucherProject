<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = ['name', 'price'];

    /**
     * Relationships
     */

    // A product can have many orders through the order_product pivot table
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product')
            ->withPivot('quantity'); // Include the quantity from the pivot table

    }

    // A product can have many redemptions (if you need to link redemptions)

}
