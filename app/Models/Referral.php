<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Referral extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'referrer_id',
        'referee_id',
        'level',
        'bonus_amount',
        'bonus_percentage',
        'total_bonus_earned',
        'status',
        'qualification_type',
        'is_qualified',
        'qualified_at',
        'is_paid',
        'paid_at',
        'transaction_id',
        'referee_registered',
        'referee_paid_initial',
        'referee_joined_constellation',
        'referee_tours_completed',
        'referred_at',
        'expires_at',
        'days_to_convert',
        'conversion_metadata',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bonus_amount' => 'decimal:2',
            'bonus_percentage' => 'decimal:2',
            'total_bonus_earned' => 'decimal:2',
            'is_qualified' => 'boolean',
            'qualified_at' => 'datetime',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
            'referee_registered' => 'boolean',
            'referee_paid_initial' => 'boolean',
            'referee_joined_constellation' => 'boolean',
            'referred_at' => 'datetime',
            'expires_at' => 'datetime',
            'conversion_metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the referrer (person who referred).
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the referee (person who was referred).
     */
    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    /**
     * Get the transaction associated with this referral bonus.
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
     * Scope a query to only include pending referrals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include qualified referrals.
     */
    public function scopeQualified($query)
    {
        return $query->where('status', 'qualified');
    }

    /**
     * Scope a query to only include paid referrals.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include direct referrals (level 1).
     */
    public function scopeDirect($query)
    {
        return $query->where('level', 1);
    }

    /**
     * Scope a query by level.
     */
    public function scopeLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted bonus amount.
     */
    public function getFormattedBonusAmountAttribute(): string
    {
        return number_format($this->bonus_amount, 2, ',', ' ') . ' €';
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'qualified' => 'blue',
            'paid' => 'green',
            'expired' => 'gray',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get qualification progress percentage.
     */
    public function getQualificationProgressAttribute(): int
    {
        $steps = [
            'referee_registered',
            'referee_paid_initial',
            'referee_joined_constellation',
        ];

        $completed = 0;
        foreach ($steps as $step) {
            if ($this->$step) {
                $completed++;
            }
        }

        return (int) round(($completed / count($steps)) * 100);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check qualification status and update.
     */
    public function checkQualification(): void
    {
        $qualified = match($this->qualification_type) {
            'registration' => $this->referee_registered,
            'first_payment' => $this->referee_paid_initial,
            'constellation_joined' => $this->referee_joined_constellation,
            'tour_completed' => $this->referee_tours_completed >= 1,
            default => false,
        };

        if ($qualified && !$this->is_qualified) {
            $this->markAsQualified();
        }
    }

    /**
     * Mark referral as qualified.
     */
    public function markAsQualified(): void
    {
        $daysToConvert = $this->referred_at->diffInDays(now());

        $this->update([
            'status' => 'qualified',
            'is_qualified' => true,
            'qualified_at' => now(),
            'days_to_convert' => $daysToConvert,
        ]);
    }

    /**
     * Mark referral bonus as paid.
     */
    public function markAsPaid(int $transactionId = null): void
    {
        $this->update([
            'status' => 'paid',
            'is_paid' => true,
            'paid_at' => now(),
            'transaction_id' => $transactionId,
        ]);

        // Update referrer's earnings
        if ($this->referrer) {
            $this->referrer->increment('referral_earnings', $this->bonus_amount);
        }
    }

    /**
     * Update referee progress.
     */
    public function updateRefereeProgress(string $milestone): void
    {
        $updates = [];

        switch ($milestone) {
            case 'registered':
                $updates['referee_registered'] = true;
                break;
            case 'paid_initial':
                $updates['referee_paid_initial'] = true;
                break;
            case 'joined_constellation':
                $updates['referee_joined_constellation'] = true;
                break;
            case 'tour_completed':
                $updates['referee_tours_completed'] = $this->referee_tours_completed + 1;
                break;
        }

        if (!empty($updates)) {
            $this->update($updates);
            $this->checkQualification();
        }
    }

    /**
     * Calculate bonus amount based on configuration.
     */
    public static function calculateBonus(User $referrer, User $referee, string $type = 'first_payment'): float
    {
        $config = config('7ensemble.referral');

        return match($type) {
            'registration' => $config['bonus_amount'] ?? 10.00,
            'first_payment' => ($referee->total_paid * ($config['bonus_percentage'] / 100)) ?? 0,
            default => 0,
        };
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

        // Set defaults on creation
        static::creating(function ($referral) {
            if (empty($referral->referred_at)) {
                $referral->referred_at = now();
            }

            if (empty($referral->level)) {
                $referral->level = 1;
            }

            // Set expiration date (90 days by default)
            if (empty($referral->expires_at)) {
                $referral->expires_at = now()->addDays(90);
            }

            // Calculate bonus if not set
            if (empty($referral->bonus_amount)) {
                $bonus = static::calculateBonus(
                    $referral->referrer,
                    $referral->referee,
                    $referral->qualification_type
                );
                $referral->bonus_amount = $bonus;
            }
        });
    }
}
