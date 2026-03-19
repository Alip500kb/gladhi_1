<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Game extends Model
{
    use HasApiTokens,Notifiable;
    protected $fillable = [
        'id',
        'title',
        'slug',
        'description',
        'created_by',
        'deleted_at',
    ];
}
