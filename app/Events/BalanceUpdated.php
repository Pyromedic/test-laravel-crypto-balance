<?php

namespace App\Events;

use App\Models\CryptoTransaction;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class BalanceUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public CryptoTransaction $transaction
    ) {}
}
