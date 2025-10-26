<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BalanceController extends Controller
{
    protected BalanceService $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * POST /api/deposit
     * Пополнение баланса пользователя
     */
    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string|max:255',
        ]);

        try {
            $balance = $this->balanceService->deposit($validated);

            return response()->json([
                'message' => 'Deposit successful',
                'user_id' => $validated['user_id'],
                'balance' => $balance->balance,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (HttpException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * POST /api/withdraw
     * Списание средств
     */
    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string|max:255',
        ]);

        try {
            $balance = $this->balanceService->withdraw($validated);

            return response()->json([
                'message' => 'Withdrawal successful',
                'user_id' => $validated['user_id'],
                'balance' => $balance->balance,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (HttpException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            $code = in_array($e->getCode(), [400, 409]) ? $e->getCode() : 400;
            return response()->json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * POST /api/transfer
     * Перевод между пользователями
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'from_user_id' => 'required|integer|min:1',
            'to_user_id' => 'required|integer|min:1|different:from_user_id',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string|max:255',
        ]);

        try {
            $this->balanceService->transfer($validated);

            return response()->json([
                'message' => 'Transfer successful',
                'from_user_id' => $validated['from_user_id'],
                'to_user_id' => $validated['to_user_id'],
                'amount' => $validated['amount'],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (HttpException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            $code = in_array($e->getCode(), [400, 409]) ? $e->getCode() : 400;
            return response()->json(['error' => $e->getMessage()], $code);
        }
    }

    /**
     * GET /api/balance/{user_id}
     * Получение текущего баланса
     */
    public function getBalance(int $userId)
    {
        try {
            $balance = $this->balanceService->getBalance($userId);

            return response()->json([
                'user_id' => $userId,
                'balance' => $balance,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
}
