<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'issuance_id',
        'used_at'
    ];

    public function issuance()
    {
        return $this->belongsTo(Issuance::class, 'issuance_id');
    }
}
