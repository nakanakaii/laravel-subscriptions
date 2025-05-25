<?php

namespace Nakanakaii\LaravelSubscriptions\Traits\Relationships;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Nakanakaii\LaravelSubscriptions\Models\Subscription;

/**
 * Trait hasSubscription
 *
 * This trait provides a relationship to the Subscription model for User/Team model.
 * It allows for easy access to the subscription data associated with the model.
 *
 * @package Nakanakaii\LaravelSubscriptions
 */
trait hasSubscription
{
    /**
     * Defines a one-to-one polymorphic relationship with the Subscription model.
     * 
     * This method allows you to access the subscription associated with the user/team.
     * 
     * @return MorphOne
     */
    public function subscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'subscriber');
    }

    /**
     * Checks if the user/team is subscribed and has an active plan.
     *
     * This method checks if the current model has an active subscription. It returns true if the
     * subscription exists and is active, otherwise it returns false.
     *
     * @return bool
     */
    public function isSubscribed(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }
}
