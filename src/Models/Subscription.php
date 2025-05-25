<?php

namespace Nakanakaii\LaravelSubscriptions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    /** Status if the subscription is active. */
    public const STATUS_ACTIVE = 'active';

    /** Status if the subscription is in trial. */
    public const STATUS_TRIAL = 'trial';

    /** Status if the subscription is pending payment. */
    public const STATUS_PENDING = 'pending';

    /** Status if the subscription is cancelled. */
    public const STATUS_CANCELLED = 'cancelled';

    /** Status if the subscription is expired. */
    public const STATUS_EXPIRED = 'expired';

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function plan(): HasOne
    {
        return $this->hasOne(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function recordInvoice($amount, $description)
    {
        $this->invoices()->create([
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    public function activate()
    {
        $this->status = self::STATUS_ACTIVE;
    }

    public function cancel()
    {
        $this->status = self::STATUS_CANCELLED;
    }

    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isMonthly(): bool
    {
        return $this->billing_cycle === 'monthly';
    }

    public function isYearly(): bool
    {
        return $this->billing_cycle === 'yearly';
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function subscriber()
    {
        return $this->morphTo('subscriber');
    }
}
