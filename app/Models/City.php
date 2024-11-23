<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    protected $fillable = [
        'name',
        'country_id'
    ];

    public function country():BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function users() 
    { 
        return $this->hasMany(User::class);
    }

    public function admins() 
    { 
        return $this->hasMany(Admin::class);
    }
}
