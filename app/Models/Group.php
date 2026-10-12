<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Group extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'groups';

    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'member_ids',
    ];
}