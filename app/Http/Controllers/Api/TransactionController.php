<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\Saving;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index()
    {
        try {
            $transactions = Transaction::where('user_id', 1)
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transactions'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transaction = Transaction::create([
                'user_id' => 1, // Later replace with auth()->id()
                'type' => $request->type,
                'category' => $request->category,
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction created successfully',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create transaction'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $transaction = Transaction::where('user_id', 1)->find($id);

            if (!$transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transaction'
            ], 500);
        }
    }

    public function monthly(Request $request)
    {
        try {
            $month = $request->query('month', now()->format('Y-m'));
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $transactions = Transaction::where('user_id', 1)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $transactions,
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                    'month' => $month
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch monthly transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $transaction = Transaction::where('user_id', 1)->find($id);

            if (!$transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|required|in:income,expense',
                'category' => 'sometimes|required|string|max:100',
                'amount' => 'sometimes|required|numeric|min:0',
                'description' => 'nullable|string|max:255',
                'date' => 'sometimes|required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $transaction->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction updated successfully',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update transaction'
            ], 500);
        }
    }

    public function evaluation(Request $request)
    {
        try {
            $month = $request->query('month');
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // 1. Get total income and expense
            $income = Transaction::where('user_id', 1)
                ->where('type', 'income')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            $expense = Transaction::where('user_id', 1)
                ->where('type', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            // 2. Get savings data for the month
            $saving = Saving::where('user_id', 1)
                ->where('month', $startDate->toDateString())
                ->first();

            // Calculate saving metrics
            $savingConsistency = 0;
            $savingPercentage = 0;
            if ($saving) {
                // Check weekly savings by looking at saved_amount changes
                $weeklySavings = DB::table('savings')
                    ->where('user_id', 1)
                    ->where('month', $startDate->toDateString())
                    ->whereRaw('saved_amount > 0')
                    ->count();

                $savingConsistency = $weeklySavings;

                // Calculate saving percentage against income
                $savingPercentage = $income > 0 ? ($saving->saved_amount / $income) * 100 : 0;
            }

            // 3. Get unexpected expenses
            $unexpectedExpense = Transaction::where('user_id', 1)
                ->where('type', 'expense')
                ->where('category', 'tak terduga')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            // 4. Count unique days with records
            $recordDays = Transaction::where('user_id', 1)
                ->whereBetween('date', [$startDate, $endDate])
                ->distinct('date')
                ->count('date');

            // 5. Get category breakdown
            $categoryBreakdown = Transaction::where('user_id', 1)
                ->where('type', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')
                ->get()
                ->mapWithKeys(function ($item) use ($expense) {
                    return [
                        $item->category => [
                            'amount' => (float)$item->total,
                            'percentage' => $expense > 0 ? ((float)$item->total / $expense) * 100 : 0
                        ]
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_income' => (float)$income,
                    'total_expense' => (float)$expense,
                    'saving_consistency' => (int)$savingConsistency,
                    'unexpected_expense' => (float)$unexpectedExpense,
                    'record_days' => (int)$recordDays,
                    'saving_percentage' => (float)$savingPercentage,
                    'saving_info' => $saving ? [
                        'target_amount' => (float)$saving->target_amount,
                        'saved_amount' => (float)$saving->saved_amount,
                        'progress' => $saving->target_amount > 0
                            ? ($saving->saved_amount / $saving->target_amount) * 100
                            : 0
                    ] : null,
                    'category_breakdown' => $categoryBreakdown,
                    'month_info' => [
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString(),
                        'total_days' => $endDate->diffInDays($startDate) + 1
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Evaluation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to calculate evaluation metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transaction = Transaction::where('user_id', 1)->find($id);

            if (!$transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction not found'
                ], 404);
            }

            $transaction->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete transaction'
            ], 500);
        }
    }
}
