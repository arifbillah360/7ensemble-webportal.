<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'language',
        'timezone',
        'currency',
        'email_notifications',
        'push_notifications',
        'sms_notifications',
        'marketing_emails',
        'notify_on_payment_received',
        'notify_on_payment_sent',
        'notify_on_tour_completed',
        'notify_on_constellation_update',
        'notify_on_referral_bonus',
        'theme',
        'date_format',
        'time_format',
        'display_currency_symbol',
        'privacy_show_earnings',
        'privacy_show_constellation',
        'privacy_show_referrals',
        'two_factor_enabled',
        'login_alerts',
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
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'marketing_emails' => 'boolean',
            'notify_on_payment_received' => 'boolean',
            'notify_on_payment_sent' => 'boolean',
            'notify_on_tour_completed' => 'boolean',
            'notify_on_constellation_update' => 'boolean',
            'notify_on_referral_bonus' => 'boolean',
            'display_currency_symbol' => 'boolean',
            'privacy_show_earnings' => 'boolean',
            'privacy_show_constellation' => 'boolean',
            'privacy_show_referrals' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'login_alerts' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user that owns this setting.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user wants to be notified for a specific event.
     */
    public function wantsNotification(string $type): bool
    {
        $field = 'notify_on_' . $type;

        return $this->$field ?? true;
    }

    /**
     * Enable all notifications.
     */
    public function enableAllNotifications(): void
    {
        $this->update([
            'email_notifications' => true,
            'push_notifications' => true,
            'notify_on_payment_received' => true,
            'notify_on_payment_sent' => true,
            'notify_on_tour_completed' => true,
            'notify_on_constellation_update' => true,
            'notify_on_referral_bonus' => true,
        ]);
    }

    /**
     * Disable all notifications.
     */
    public function disableAllNotifications(): void
    {
        $this->update([
            'email_notifications' => false,
            'push_notifications' => false,
            'sms_notifications' => false,
            'notify_on_payment_received' => false,
            'notify_on_payment_sent' => false,
            'notify_on_tour_completed' => false,
            'notify_on_constellation_update' => false,
            'notify_on_referral_bonus' => false,
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
        static::creating(function ($setting) {
            $defaults = [
                'language' => 'fr',
                'timezone' => 'Europe/Zurich',
                'currency' => 'EUR',
                'email_notifications' => true,
                'push_notifications' => true,
                'sms_notifications' => false,
                'marketing_emails' => true,
                'notify_on_payment_received' => true,
                'notify_on_payment_sent' => true,
                'notify_on_tour_completed' => true,
                'notify_on_constellation_update' => true,
                'notify_on_referral_bonus' => true,
                'theme' => 'cosmic',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'display_currency_symbol' => true,
                'privacy_show_earnings' => true,
                'privacy_show_constellation' => true,
                'privacy_show_referrals' => true,
                'two_factor_enabled' => false,
                'login_alerts' => true,
            ];

            foreach ($defaults as $key => $value) {
                if (!isset($setting->$key)) {
                    $setting->$key = $value;
                }
            }
        });
    }
}
