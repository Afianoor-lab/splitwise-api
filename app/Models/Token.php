<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Token extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'tokens';

    protected $fillable = [
        'user_id',
        'api_token',
    ];
}