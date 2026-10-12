<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Settlement extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'settlements';

    protected $fillable = [
        'group_id',
        'paid_by',
        'paid_to',
        'amount',
        'note',
        'attachment_url',
    ];
}