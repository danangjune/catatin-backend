<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingLog extends Model
{
    protected $fillable = [
        'user_id',
        'saving_id',
        'date',
        'amount',
    ];
}
