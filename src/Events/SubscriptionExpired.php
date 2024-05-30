<?php

namespace Nakanakaii\LaravelSubscriptions\Events;

use Nakanakaii\LaravelSubscriptions\Models\Subscription;

/**
 * The SubscriptionExpired event class represents an event that is triggered when a subscription expires.
 *
 * This event is typically dispatched after a subscription reaches its end date and becomes inactive. It allows other parts
 * of your application to react to the subscription expiration, such as sending reminder emails, downgrading user access,
 * or notifying administrators.
 */
class SubscriptionExpired
{
    /**
     * The subscription object that has expired.
     *
     * @var Subscription
     */
    public $subscription;

    /**
     * Constructor for the event.
     *
     * This constructor takes an instance of the `Subscription` model representing the expired subscription.
     *
     * @param  Subscription  $subscription  The subscription object.
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}
