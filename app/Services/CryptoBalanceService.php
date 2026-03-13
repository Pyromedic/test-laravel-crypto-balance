<?php

namespace App\Services;

use App\Models\CryptoTransaction;
use App\Models\User;
use App\Events\BalanceUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CryptoBalanceService
{
    public function credit(
        int $userId,
        string $amount,
        string $currency = 'USDT',
        string $notes = '',
        ?string $txHash = null,
        string $status = 'confirmed'
    ): CryptoTransaction {
        if (bccomp($amount, '0', 8) <= 0) {
            throw new Exception('Сумма должна быть > 0');
        }

        return DB::transaction(function () use ($userId, $amount, $currency, $notes, $txHash, $status) {
            $user = User::lockForUpdate()->findOrFail($userId);

            $newBalance = bcadd((string)$user->crypto_balance, $amount, 8);
            $user->crypto_balance = $newBalance;
            $user->save();

            $tx = CryptoTransaction::create([
                'user_id' => $userId,
                'type' => 'credit',
                'amount' => $amount,
                'currency' => $currency,
                'status' => $status,
                'tx_hash' => $txHash,
                'notes' => $notes,
            ]);

            Log::info('Crypto credit', ['user_id' => $userId, 'amount' => $amount, 'tx_id' => $tx->id]);

            event(new BalanceUpdated($user, $tx)); 

            return $tx;
        });
    }

    public function debit(
        int $userId,
        string $amount,
        string $currency = 'USDT',
        string $notes = '',
        ?string $txHash = null
    ): CryptoTransaction {
        if (bccomp($amount, '0', 8) <= 0) {
            throw new Exception('Сумма должна быть > 0');
        }

        return DB::transaction(function () use ($userId, $amount, $currency, $notes, $txHash) {
            $user = User::lockForUpdate()->findOrFail($userId);

            if (bccomp((string)$user->crypto_balance, $amount, 8) < 0) {
                throw new Exception('Недостаточно средств');
            }

            $newBalance = bcsub((string)$user->crypto_balance, $amount, 8);
            $user->crypto_balance = $newBalance;
            $user->save();

            $tx = CryptoTransaction::create([
                'user_id' => $userId,
                'type' => 'debit',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'tx_hash' => $txHash,
                'notes' => $notes,
            ]);

            Log::info('Crypto debit', ['user_id' => $userId, 'amount' => $amount, 'tx_id' => $tx->id]);

            event(new BalanceUpdated($user, $tx));

            return $tx;
        });
    }
}
