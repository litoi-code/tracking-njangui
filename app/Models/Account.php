<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_type_id',
        'balance',
        'description',
        'currency',
        'user_id'
    ];

    protected $casts = [
        'balance' => 'decimal:0'
    ];

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_account_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_account_id');
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2) . ' ' . $this->currency;
    }

    public function getMonthlyTransferVolume()
    {
        $outgoing = $this->outgoingTransfers()
            ->whereMonth('executed_at', now()->month)
            ->sum('amount');

        $incoming = $this->incomingTransfers()
            ->whereMonth('executed_at', now()->month)
            ->sum('amount');

        return [
            'outgoing' => $outgoing,
            'incoming' => $incoming,
            'net' => $incoming - $outgoing
        ];
    }

    public function getTransferHistory($months = 3)
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        $outgoing = $this->outgoingTransfers()
            ->where('executed_at', '>=', $startDate)
            ->orderBy('executed_at', 'desc')
            ->get();

        $incoming = $this->incomingTransfers()
            ->where('executed_at', '>=', $startDate)
            ->orderBy('executed_at', 'desc')
            ->get();

        return [
            'outgoing' => $outgoing,
            'incoming' => $incoming
        ];
    }

    public function adjustBalance($amount)
    {
        $this->balance += $amount;
        $this->save();
        
        return $this->fresh();
    }
}
