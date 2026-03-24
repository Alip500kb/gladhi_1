<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class game_version extends Model
{
    protected $table = 'game_versions';
    protected $fillable = [
        'id',
        'game_id',
        'version',
        'storage_path'
    ];
}
