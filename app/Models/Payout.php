<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payout extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tour_id',
        'transaction_id',
        'amount',
        'currency',
        'fee',
        'net_amount',
        'payment_method_type',
        'bank_account',
        'iban',
        'account_holder_name',
        'paypal_email',
        'mobile_money_number',
        'crypto_wallet_address',
        'status',
        'processed_at',
        'completed_at',
        'failed_at',
        'rejected_at',
        'failure_reason',
        'rejection_reason',
        'admin_notes',
        'reference_number',
        'gateway_reference',
        'proof_url',
        'metadata',
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
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'rejected_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user that owns this payout.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tour associated with this payout.
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Get the transaction associated with this payout.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include pending payouts.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing payouts.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include completed payouts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed payouts.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
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
            'rejected' => 'orange',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Check if payout is complete.
     */
    public function getIsCompleteAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payout is pending.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Mark payout as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark payout as completed.
     */
    public function markAsCompleted(string $gatewayReference = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'gateway_reference' => $gatewayReference,
        ]);
    }

    /**
     * Mark payout as failed.
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Mark payout as rejected.
     */
    public function markAsRejected(string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
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

        // Calculate net amount and generate reference on creation
        static::creating(function ($payout) {
            if (empty($payout->net_amount)) {
                $payout->net_amount = $payout->amount - ($payout->fee ?? 0);
            }

            if (empty($payout->currency)) {
                $payout->currency = 'EUR';
            }

            if (empty($payout->reference_number)) {
                $payout->reference_number = 'PYT-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }
}
