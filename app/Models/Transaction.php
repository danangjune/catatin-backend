<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';
    const TYPE_SAVINGS = 'savings';
    const CATEGORY_UNEXPECTED = 'tak terduga';

    protected $fillable = [
        'user_id',
        'category',
        'type',
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
