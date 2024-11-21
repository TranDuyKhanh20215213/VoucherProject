<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issuance extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'is_active',
        'user_id',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function redemption()
    {
        return $this->hasOne(Redemption::class);
    }
}
