<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConstellationMember extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'constellation_id',
        'user_id',
        'position',
        'role',
        'status',
        'joined_at',
        'left_at',
        'is_active',
        'total_contributed',
        'total_received',
        'tours_completed',
        'current_tour',
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
            'is_active' => 'boolean',
            'total_contributed' => 'decimal:2',
            'total_received' => 'decimal:2',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the constellation that this member belongs to.
     */
    public function constellation()
    {
        return $this->belongsTo(Constellation::class);
    }

    /**
     * Get the user for this constellation member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include active members.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    /**
     * Scope a query to only include waiting members.
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope a query to only include members by position.
     */
    public function scopePosition($query, $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope a query to only include Alcyones.
     */
    public function scopeAlcyones($query)
    {
        return $query->where('role', 'alcyone');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the member's role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'alcyone' => 'Alcyone',
            'member' => 'Membre',
            default => 'Membre',
        };
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'waiting' => 'yellow',
            'inactive' => 'gray',
            'left' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get net earnings (received - contributed).
     */
    public function getNetEarningsAttribute(): float
    {
        return $this->total_received - $this->total_contributed;
    }

    /**
     * Get position label.
     */
    public function getPositionLabelAttribute(): string
    {
        return "Position #{$this->position}";
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if member is Alcyone.
     */
    public function isAlcyone(): bool
    {
        return $this->role === 'alcyone';
    }

    /**
     * Mark member as active.
     */
    public function markAsActive(): void
    {
        $this->update([
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    /**
     * Mark member as left.
     */
    public function markAsLeft(string $reason = null): void
    {
        $this->update([
            'status' => 'left',
            'is_active' => false,
            'left_at' => now(),
            'notes' => $reason,
        ]);
    }

    /**
     * Promote to Alcyone.
     */
    public function promoteToAlcyone(): void
    {
        $this->update([
            'role' => 'alcyone',
        ]);

        // Update user record
        if ($this->user) {
            $this->user->update([
                'is_alcyone' => true,
            ]);
        }
    }

    /**
     * Demote from Alcyone.
     */
    public function demoteFromAlcyone(): void
    {
        $this->update([
            'role' => 'member',
        ]);

        // Update user record
        if ($this->user) {
            $this->user->update([
                'is_alcyone' => false,
            ]);
        }
    }

    /**
     * Update contribution.
     */
    public function addContribution(float $amount): void
    {
        $this->increment('total_contributed', $amount);
    }

    /**
     * Update receipt.
     */
    public function addReceipt(float $amount): void
    {
        $this->increment('total_received', $amount);
    }

    /**
     * Complete a tour.
     */
    public function completeTour(): void
    {
        $this->increment('tours_completed');
        if ($this->current_tour < 7) {
            $this->increment('current_tour');
        }
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
        static::creating(function ($member) {
            if (empty($member->joined_at)) {
                $member->joined_at = now();
            }

            if (empty($member->status)) {
                $member->status = 'waiting';
            }

            if (empty($member->role)) {
                $member->role = 'member';
            }

            if (empty($member->current_tour)) {
                $member->current_tour = 1;
            }
        });

        // Update constellation member count
        static::created(function ($member) {
            $member->constellation->increment('current_members');
        });

        static::deleted(function ($member) {
            $member->constellation->decrement('current_members');
        });
    }
}
