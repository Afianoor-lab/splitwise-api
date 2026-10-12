<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Expense extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'expenses';

    protected $fillable = [
        'group_id',
        'description',
        'amount',
        'paid_by',
        'split_type',
        'participants',
    ];
}