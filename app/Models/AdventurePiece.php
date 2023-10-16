<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdventurePiece extends Model
{
    use HasFactory;

    protected $fillable = [
        'sessionId',
        'role',
        'content',
        'order'
    ];


    public function AdventureSession()
    {
        return $this->belongsTo(AdventureSession::class, 'sessionId', 'id');
    }
}
