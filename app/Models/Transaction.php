<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'type', // pemasukan atau pengeluaran
        'amount',
        'description',
        'date',
    ];

    protected $dates = ['date'];

    // Relasi dengan user (jika ada)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
