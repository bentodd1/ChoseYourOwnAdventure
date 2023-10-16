<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdventureSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'ip_address',
        'mac_address',
        'isActive',

    ];

    public function adventurePieces()
    {
        return $this->hasMany(AdventurePiece::class, 'sessionId','id');
    }


}
