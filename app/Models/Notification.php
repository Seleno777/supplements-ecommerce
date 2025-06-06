<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message',
        'type',
        'read_at',
    ];
    

    protected $dates = ['read_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
