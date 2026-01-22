<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourRequirement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tour_number',
        'option_type',
        'amount_to_pay',
        'amount_to_receive',
        'amount_to_keep',
        'required_members',
        'description',
        'benefits',
        'rules',
        'is_active',
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
            'amount_to_receive' => 'decimal:2',
            'amount_to_keep' => 'decimal:2',
            'is_active' => 'boolean',
            'benefits' => 'array',
            'rules' => 'array',
            'metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get tours using this requirement.
     */
    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include active requirements.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query by constellation type.
     */
    public function scopeForType($query, $type)
    {
        return $query->where('option_type', $type);
    }

    /**
     * Scope a query by tour number.
     */
    public function scopeTourNumber($query, $number)
    {
        return $query->where('tour_number', $number);
    }

    /**
     * Scope a query to get Triangulum requirements.
     */
    public function scopeTriangulum($query)
    {
        return $query->where('option_type', 'triangulum');
    }

    /**
     * Scope a query to get Pléiades requirements.
     */
    public function scopePleiades($query)
    {
        return $query->where('option_type', 'pleiades');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted amount to pay.
     */
    public function getFormattedAmountToPayAttribute(): string
    {
        return number_format($this->amount_to_pay, 2, ',', ' ') . ' €';
    }

    /**
     * Get formatted amount to receive.
     */
    public function getFormattedAmountToReceiveAttribute(): string
    {
        return number_format($this->amount_to_receive, 2, ',', ' ') . ' €';
    }

    /**
     * Get formatted amount to keep.
     */
    public function getFormattedAmountToKeepAttribute(): string
    {
        return number_format($this->amount_to_keep, 2, ',', ' ') . ' €';
    }

    /**
     * Get the tour name.
     */
    public function getTourNameAttribute(): string
    {
        return "Tour {$this->tour_number}";
    }

    /**
     * Get the constellation type label.
     */
    public function getConstellationTypeLabelAttribute(): string
    {
        return match($this->option_type) {
            'triangulum' => 'Triangulum',
            'pleiades' => 'Les Pléiades',
            default => ucfirst($this->option_type),
        };
    }

    /**
     * Get ROI percentage.
     */
    public function getRoiPercentageAttribute(): float
    {
        if ($this->amount_to_pay == 0) {
            return 0;
        }

        return round(($this->amount_to_keep / $this->amount_to_pay) * 100, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get requirement for specific tour and type.
     */
    public static function getRequirement(int $tourNumber, string $type): ?self
    {
        return static::where('tour_number', $tourNumber)
                     ->where('option_type', $type)
                     ->where('is_active', true)
                     ->first();
    }

    /**
     * Get all requirements for a constellation type.
     */
    public static function getAllForType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('option_type', $type)
                     ->where('is_active', true)
                     ->orderBy('tour_number')
                     ->get();
    }

    /**
     * Calculate total earnings for completing all tours.
     */
    public static function getTotalEarnings(string $type): float
    {
        return static::where('option_type', $type)
                     ->where('is_active', true)
                     ->sum('amount_to_keep');
    }

    /**
     * Get multiplier for this tour.
     */
    public function getMultiplierAttribute(): float
    {
        if ($this->amount_to_pay == 0) {
            return 0;
        }

        return round($this->amount_to_receive / $this->amount_to_pay, 2);
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

        // Calculate amount_to_keep on save
        static::saving(function ($requirement) {
            if (!empty($requirement->amount_to_receive) && !empty($requirement->amount_to_pay)) {
                $requirement->amount_to_keep = $requirement->amount_to_receive - $requirement->amount_to_pay;
            }

            // Set required members based on type
            if (empty($requirement->required_members)) {
                $requirement->required_members = $requirement->option_type === 'triangulum' ? 3 : 7;
            }
        });
    }
}
