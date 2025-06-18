<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Saving;
use App\Models\SavingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SavingController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $savings = Saving::where('user_id', $userId)
            ->orderBy('month', 'desc')
            ->get();

        return response()->json($savings);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target_amount' => 'required|integer|min:0',
            'saved_amount' => 'required|integer|min:0',
            'month' => 'required|date',
        ]);

        $data['user_id'] = $request->user()->id;

        $saving = Saving::create($data);

        // Jika ada tabungan awal, catat log juga
        if ($data['saved_amount'] > 0) {
            SavingLog::create([
                'user_id' => $data['user_id'],
                'saving_id' => $saving->id,
                'date' => Carbon::parse($data['month'])->toDateString(),
                'amount' => $data['saved_amount'],
            ]);
        }

        return response()->json($saving, 201);
    }

    public function showByMonth(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->query('month') ?? now()->startOfMonth()->toDateString();

        $saving = Saving::where('user_id', $userId)
            ->where('month', $month)
            ->first();

        if (!$saving) {
            return response()->json(null, 404);
        }

        return response()->json($saving);
    }

    public function logs(Request $request)
    {
        $userId = $request->user()->id;

        $logs = SavingLog::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($logs);
    }

    public function update(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;

            $saving = Saving::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $validated = $request->validate([
                'target_amount' => 'nullable|integer|min:0',
                'add_amount' => 'nullable|integer|min:0',
            ]);

            if (isset($validated['target_amount'])) {
                $saving->target_amount = $validated['target_amount'];
            }

            if (isset($validated['add_amount'])) {
                $saving->saved_amount += $validated['add_amount'];

                // ⬇️ Tambahkan log setiap kali user menabung
                SavingLog::create([
                    'user_id' => $userId,
                    'saving_id' => $saving->id,
                    'date' => now()->toDateString(),
                    'amount' => $validated['add_amount'],
                ]);
            }

            $saving->save();

            return response()->json($saving);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Saving not found'], 404);
        }
    }
}
