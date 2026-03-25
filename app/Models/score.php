<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class score extends Model
{
    use HasApiTokens,Notifiable;
    public $timestamps = false;
    protected $fillable = [
        'id',
        'user_id',
        'game_version_id',
        'score'
    ];
}
