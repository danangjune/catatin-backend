<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Saving;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        try {
            $user = $request->user(); // atau auth()->user()
            $now = now();

            $monthlyStats = [
                'income' => Transaction::where('user_id', $user->id)
                    ->whereMonth('date', $now->month)
                    ->whereYear('date', $now->year)
                    ->where('type', 'income')
                    ->sum('amount'),

                'expense' => Transaction::where('user_id', $user->id)
                    ->whereMonth('date', $now->month)
                    ->whereYear('date', $now->year)
                    ->where('type', 'expense')
                    ->sum('amount'),

                'savings' => Saving::where('user_id', $user->id)
                    ->whereYear('month', $now->year)
                    ->whereMonth('month', $now->month)
                    ->value('saved_amount') ?? 0,
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'monthly_stats' => $monthlyStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch profile data: ' . $e->getMessage()
            ], 500);
        }
    }
}
