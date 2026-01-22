<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Referral extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'status',
        'reward_amount',
        'referred_reward_amount',
        'reward_claimed',
        'referred_reward_claimed',
        'completed_at',
        'expires_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reward_amount' => 'decimal:2',
            'referred_reward_amount' => 'decimal:2',
            'reward_claimed' => 'boolean',
            'referred_reward_claimed' => 'boolean',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user who made the referral.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who was referred.
     */
    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    /**
     * Generate a unique referral code.
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Check if the referral is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the referral is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the referral is expired.
     */
    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Check if the referral is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Mark the referral as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the referral as expired.
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Mark the referral as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Mark the referrer's reward as claimed.
     */
    public function claimReferrerReward(): void
    {
        $this->update(['reward_claimed' => true]);
    }

    /**
     * Mark the referred user's reward as claimed.
     */
    public function claimReferredReward(): void
    {
        $this->update(['referred_reward_claimed' => true]);
    }

    /**
     * Scope a query to only include pending referrals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed referrals.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include expired referrals.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope a query to only include cancelled referrals.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include referrals with unclaimed rewards.
     */
    public function scopeUnclaimedRewards($query)
    {
        return $query->where('status', 'completed')
            ->where(function ($q) {
                $q->where('reward_claimed', false)
                    ->orWhere('referred_reward_claimed', false);
            });
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning',
            'completed' => 'bg-success',
            'expired' => 'bg-secondary',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the total reward amount (referrer + referred).
     */
    public function getTotalRewardAmount(): float
    {
        return $this->reward_amount + $this->referred_reward_amount;
    }
}
