<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'payment_method',
        'payu_subscription_id',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'cancelled_at'         => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // --- Scopes ---

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['trial', 'active']);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 5): Builder
    {
        return $query->whereIn('status', ['trial', 'active'])
            ->where('current_period_end', '<=', now()->addDays($days));
    }

    // --- Helpers ---

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active']);
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function daysRemaining(): int
    {
        if (!$this->current_period_end) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->current_period_end, false));
    }
}
