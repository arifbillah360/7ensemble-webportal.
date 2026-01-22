<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'user_id',
        'tour_id',
        'constellation_id',
        'payment_method_id',
        'type',
        'direction',
        'amount',
        'currency',
        'fee',
        'net_amount',
        'related_user_id',
        'related_user_name',
        'payment_gateway',
        'gateway_transaction_id',
        'gateway_reference',
        'gateway_response',
        'status',
        'bank_reference',
        'bank_account',
        'requires_verification',
        'is_verified',
        'verified_at',
        'verified_by',
        'receipt_url',
        'proof_of_payment_url',
        'processed_at',
        'completed_at',
        'failed_at',
        'refunded_at',
        'failure_reason',
        'failure_message',
        'description',
        'admin_notes',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'requires_verification' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'refunded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user that owns this transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tour associated with this transaction.
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Get the constellation associated with this transaction.
     */
    public function constellation()
    {
        return $this->belongsTo(Constellation::class);
    }

    /**
     * Get the payment method used for this transaction.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the related user (e.g., Alcyone, recipient).
     */
    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Get the user who verified this transaction.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include payments.
     */
    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    /**
     * Scope a query to only include payouts.
     */
    public function scopePayouts($query)
    {
        return $query->where('type', 'payout');
    }

    /**
     * Scope a query to only include credits (money in).
     */
    public function scopeCredits($query)
    {
        return $query->where('direction', 'credit');
    }

    /**
     * Scope a query to only include debits (money out).
     */
    public function scopeDebits($query)
    {
        return $query->where('direction', 'debit');
    }

    /**
     * Scope a query to only include verified transactions.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include transactions requiring verification.
     */
    public function scopeRequiringVerification($query)
    {
        return $query->where('requires_verification', true)
                     ->where('is_verified', false);
    }

    /**
     * Scope a query by payment gateway.
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    /**
     * Scope a query by transaction type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted amount with currency.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, ',', ' ') . ' ' . $this->currency;
    }

    /**
     * Get formatted net amount with currency.
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return number_format($this->net_amount, 2, ',', ' ') . ' ' . $this->currency;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            'refunded' => 'orange',
            'cancelled' => 'gray',
            'on_hold' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get type label in French.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'payment' => 'Paiement',
            'payout' => 'Retrait',
            'transfer' => 'Transfert',
            'referral_bonus' => 'Bonus de parrainage',
            'admin_fee' => 'Frais administratifs',
            'refund' => 'Remboursement',
            'initial_payment' => 'Paiement initial',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get direction icon.
     */
    public function getDirectionIconAttribute(): string
    {
        return $this->direction === 'credit' ? '↓' : '↑';
    }

    /**
     * Check if transaction is complete.
     */
    public function getIsCompleteAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is pending.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction has failed.
     */
    public function getHasFailedAttribute(): bool
    {
        return $this->status === 'failed';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Mark transaction as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'processed_at' => $this->processed_at ?? now(),
        ]);

        // Update user balances
        if ($this->direction === 'credit') {
            $this->user->increment('total_received', $this->net_amount);
            $this->user->increment('available_balance', $this->net_amount);
        } else {
            $this->user->increment('total_paid', $this->net_amount);
            $this->user->decrement('available_balance', $this->net_amount);
        }

        $this->user->save();
    }

    /**
     * Mark transaction as failed.
     */
    public function markAsFailed(string $reason = null, string $message = null): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
            'failure_message' => $message,
        ]);
    }

    /**
     * Mark transaction as verified.
     */
    public function markAsVerified(?int $verifiedBy = null): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $verifiedBy ?? auth()->id(),
        ]);
    }

    /**
     * Refund this transaction.
     */
    public function refund(string $reason = null): self
    {
        // Create a refund transaction
        $refund = static::create([
            'transaction_id' => static::generateTransactionId(),
            'user_id' => $this->user_id,
            'tour_id' => $this->tour_id,
            'constellation_id' => $this->constellation_id,
            'type' => 'refund',
            'direction' => $this->direction === 'debit' ? 'credit' : 'debit',
            'amount' => $this->amount,
            'currency' => $this->currency,
            'fee' => 0,
            'net_amount' => $this->amount,
            'status' => 'completed',
            'description' => "Remboursement: {$this->transaction_id}",
            'admin_notes' => $reason,
            'metadata' => [
                'original_transaction_id' => $this->id,
                'refund_reason' => $reason,
            ],
            'completed_at' => now(),
        ]);

        // Mark original as refunded
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        return $refund;
    }

    /**
     * Generate unique transaction ID.
     */
    public static function generateTransactionId(): string
    {
        do {
            $id = 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(10));
        } while (static::where('transaction_id', $id)->exists());

        return $id;
    }

    /*
    |--------------------------------------------------------------------------
    | MODEL EVENTS
    |--------------------------------------------------------------------------
    */

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate transaction ID on creation
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = static::generateTransactionId();
            }

            // Calculate net amount if not set
            if (empty($transaction->net_amount)) {
                $transaction->net_amount = $transaction->amount - $transaction->fee;
            }

            // Set currency default
            if (empty($transaction->currency)) {
                $transaction->currency = 'EUR';
            }
        });
    }
}
