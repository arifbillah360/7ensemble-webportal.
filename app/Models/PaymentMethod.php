<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'provider',
        'provider_payment_method_id',
        'card_brand',
        'card_last_four',
        'card_exp_month',
        'card_exp_year',
        'bank_name',
        'account_holder_name',
        'iban',
        'bic_swift',
        'mobile_money_provider',
        'mobile_number',
        'crypto_currency',
        'crypto_wallet_address',
        'encrypted_details',
        'is_default',
        'is_verified',
        'verified_at',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'encrypted_details',
        'iban',
        'crypto_wallet_address',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user that owns this payment method.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get transactions using this payment method.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include verified payment methods.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include default payment methods.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query by payment method type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the display name for this payment method.
     */
    public function getDisplayNameAttribute(): string
    {
        return match($this->type) {
            'card' => $this->card_brand . ' •••• ' . $this->card_last_four,
            'bank_transfer' => $this->bank_name . ' - ' . $this->account_holder_name,
            'paypal' => 'PayPal',
            'mobile_money' => $this->mobile_money_provider,
            'crypto' => $this->crypto_currency . ' Wallet',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the icon for this payment method type.
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'card' => '💳',
            'bank_transfer' => '🏦',
            'paypal' => '🅿️',
            'mobile_money' => '📱',
            'crypto' => '₿',
            default => '💰',
        };
    }

    /**
     * Get masked IBAN (show only last 4 characters).
     */
    public function getMaskedIbanAttribute(): ?string
    {
        if (!$this->iban) {
            return null;
        }

        $length = strlen($this->iban);
        return str_repeat('•', $length - 4) . substr($this->iban, -4);
    }

    /**
     * Get masked mobile number.
     */
    public function getMaskedMobileNumberAttribute(): ?string
    {
        if (!$this->mobile_number) {
            return null;
        }

        $length = strlen($this->mobile_number);
        return str_repeat('•', $length - 4) . substr($this->mobile_number, -4);
    }

    /**
     * Check if card is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if ($this->type !== 'card' || !$this->card_exp_month || !$this->card_exp_year) {
            return false;
        }

        $expiryDate = \Carbon\Carbon::createFromFormat('m/Y', $this->card_exp_month . '/' . $this->card_exp_year);
        return $expiryDate->isPast();
    }

    /**
     * Encrypt sensitive details.
     */
    public function setEncryptedDetailsAttribute($value): void
    {
        if ($value) {
            $this->attributes['encrypted_details'] = Crypt::encryptString(json_encode($value));
        }
    }

    /**
     * Decrypt sensitive details.
     */
    public function getEncryptedDetailsAttribute($value): ?array
    {
        if (!$value) {
            return null;
        }

        try {
            return json_decode(Crypt::decryptString($value), true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Set this payment method as default.
     */
    public function setAsDefault(): void
    {
        // Remove default from all other payment methods for this user
        static::where('user_id', $this->user_id)
              ->where('id', '!=', $this->id)
              ->update(['is_default' => false]);

        // Set this one as default
        $this->update(['is_default' => true]);
    }

    /**
     * Mark as verified.
     */
    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Check if payment method can be used.
     */
    public function canBeUsed(): bool
    {
        return $this->status === 'active'
            && !$this->is_expired
            && ($this->type !== 'card' || $this->is_verified);
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

        // If this is the first payment method, make it default
        static::creating(function ($paymentMethod) {
            $hasDefault = static::where('user_id', $paymentMethod->user_id)
                               ->where('is_default', true)
                               ->exists();

            if (!$hasDefault) {
                $paymentMethod->is_default = true;
            }
        });

        // Update expired status for cards
        static::saving(function ($paymentMethod) {
            if ($paymentMethod->type === 'card' && $paymentMethod->is_expired) {
                $paymentMethod->status = 'expired';
            }
        });
    }
}
