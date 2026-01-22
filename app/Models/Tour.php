<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tour extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'constellation_id',
        'tour_requirement_id',
        'tour_number',
        'constellation_type',
        'amount_to_pay',
        'amount_paid',
        'amount_to_receive',
        'amount_received',
        'amount_kept',
        'payment_status',
        'payment_due_date',
        'payment_completed_at',
        'receipt_status',
        'receipt_expected_date',
        'receipt_completed_at',
        'status',
        'alcyone_id',
        'is_alcyone',
        'members_paid',
        'required_members',
        'started_at',
        'completed_at',
        'failed_at',
        'duration_days',
        'notes',
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
            'amount_to_pay' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'amount_to_receive' => 'decimal:2',
            'amount_received' => 'decimal:2',
            'amount_kept' => 'decimal:2',
            'is_alcyone' => 'boolean',
            'payment_due_date' => 'datetime',
            'payment_completed_at' => 'datetime',
            'receipt_expected_date' => 'datetime',
            'receipt_completed_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user that owns this tour.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the constellation for this tour.
     */
    public function constellation()
    {
        return $this->belongsTo(Constellation::class);
    }

    /**
     * Get the tour requirement (template).
     */
    public function tourRequirement()
    {
        return $this->belongsTo(TourRequirement::class);
    }

    /**
     * Get the Alcyone (receiver) for this tour.
     */
    public function alcyone()
    {
        return $this->belongsTo(User::class, 'alcyone_id');
    }

    /**
     * Get all transactions for this tour.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get pending transactions for this tour.
     */
    public function pendingTransactions()
    {
        return $this->hasMany(Transaction::class)->where('status', 'pending');
    }

    /**
     * Get completed transactions for this tour.
     */
    public function completedTransactions()
    {
        return $this->hasMany(Transaction::class)->where('status', 'completed');
    }

    /**
     * Get payouts for this tour.
     */
    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include active tours.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include completed tours.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending tours.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include tours where user is Alcyone.
     */
    public function scopeAsAlcyone($query)
    {
        return $query->where('is_alcyone', true);
    }

    /**
     * Scope a query to only include tours by tour number.
     */
    public function scopeTourNumber($query, $number)
    {
        return $query->where('tour_number', $number);
    }

    /**
     * Scope a query to only include tours by constellation type.
     */
    public function scopeConstellationType($query, $type)
    {
        return $query->where('constellation_type', $type);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the completion percentage.
     */
    public function getCompletionPercentageAttribute(): int
    {
        if ($this->required_members == 0) {
            return 0;
        }

        return (int) round(($this->members_paid / $this->required_members) * 100);
    }

    /**
     * Check if payment is complete.
     */
    public function getIsPaymentCompleteAttribute(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if receipt is complete.
     */
    public function getIsReceiptCompleteAttribute(): bool
    {
        return $this->receipt_status === 'received' || $this->receipt_status === 'completed';
    }

    /**
     * Get remaining amount to pay.
     */
    public function getRemainingToPayAttribute(): float
    {
        return max(0, $this->amount_to_pay - $this->amount_paid);
    }

    /**
     * Get remaining amount to receive.
     */
    public function getRemainingToReceiveAttribute(): float
    {
        return max(0, $this->amount_to_receive - $this->amount_received);
    }

    /**
     * Get tour name.
     */
    public function getTourNameAttribute(): string
    {
        return "Tour {$this->tour_number}";
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'gray',
            'active', 'in_progress' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            'cancelled' => 'yellow',
            default => 'gray',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if tour is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if tour is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'in_progress';
    }

    /**
     * Check if user is Alcyone for this tour.
     */
    public function userIsAlcyone(): bool
    {
        return $this->is_alcyone;
    }

    /**
     * Mark tour as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark tour as completed.
     */
    public function markAsCompleted(): void
    {
        $started = $this->started_at ?? now();
        $duration = now()->diffInDays($started);

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_days' => $duration,
        ]);
    }

    /**
     * Mark tour as failed.
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'notes' => $reason,
        ]);
    }

    /**
     * Update payment progress.
     */
    public function updatePaymentProgress(float $amount): void
    {
        $this->increment('amount_paid', $amount);
        $this->increment('members_paid');

        if ($this->amount_paid >= $this->amount_to_pay) {
            $this->update([
                'payment_status' => 'paid',
                'payment_completed_at' => now(),
            ]);
        } else {
            $this->update([
                'payment_status' => 'partial',
            ]);
        }
    }

    /**
     * Update receipt progress.
     */
    public function updateReceiptProgress(float $amount): void
    {
        $this->increment('amount_received', $amount);

        if ($this->amount_received >= $this->amount_to_receive) {
            $this->update([
                'receipt_status' => 'completed',
                'receipt_completed_at' => now(),
            ]);
        } else {
            $this->update([
                'receipt_status' => 'partial',
            ]);
        }

        // Calculate amount kept (net earnings)
        $this->update([
            'amount_kept' => $this->amount_received - $this->amount_paid,
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

        // Set default values on creation
        static::creating(function ($tour) {
            if (empty($tour->status)) {
                $tour->status = 'pending';
            }

            if (empty($tour->payment_status)) {
                $tour->payment_status = 'pending';
            }

            if (empty($tour->receipt_status)) {
                $tour->receipt_status = 'waiting';
            }

            // Set required members based on constellation type
            if (empty($tour->required_members)) {
                $tour->required_members = $tour->constellation_type === 'triangulum' ? 3 : 7;
            }
        });

        // Update user's current tour when tour is completed
        static::updated(function ($tour) {
            if ($tour->status === 'completed' && $tour->wasChanged('status')) {
                $user = $tour->user;

                // Only advance if this is the current tour
                if ($user && $user->current_tour == $tour->tour_number && $tour->tour_number < 7) {
                    $user->increment('current_tour');
                }
            }
        });
    }
}
