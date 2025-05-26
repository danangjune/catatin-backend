<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_amount',
        'saved_amount',
        'month',
    ];

    protected $dates = ['month'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
