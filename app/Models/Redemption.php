<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'issuance_id',
    ];

    public function issuance()
    {
        return $this->belongsTo(Issuance::class, 'issuance_id');
    }
}
