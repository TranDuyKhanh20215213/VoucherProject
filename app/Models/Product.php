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
    public $timestamps = false;
    protected $fillable = ['name', 'price'];

    /**
     * Relationships
     */

    // A product can have many redemptions
    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    // A product can be eligible for many vouchers
    public function eligibilities()
    {
        return $this->hasMany(Eligibility::class);
    }
}
