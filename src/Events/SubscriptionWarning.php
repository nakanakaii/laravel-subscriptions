<?php

namespace Nakanakaii\LaravelSubscriptions\Events;

use Nakanakaii\LaravelSubscriptions\Models\Subscription;

/**
 * The SubscriptionWarning event class represents an event that is triggered when a subscription is nearing its expiration date.
 *
 * This event is typically dispatched by the `CheckSubscription` command (or similar logic) to notify other parts
 * of your application that a subscription is about to expire. It allows you to take actions such as sending reminder
 * emails to the user, prompting them to renew their subscription, or displaying warnings in the user interface.
 */
class SubscriptionWarning
{
    /**
     * The number of days remaining until the subscription expires.
     */
    public int $daysUntilExpiration;

    /**
     * The subscription object that is nearing expiration.
     */
    public Subscription $subscription;

    /**
     * Constructor for the event.
     *
     * This constructor takes an instance of the `Subscription` model representing the expiring subscription
     * and the number of days remaining until expiration.
     *
     * @param  Subscription  $subscription  The subscription object.
     * @param  int  $daysUntilExpiration  The number of days remaining until expiration.
     */
    public function __construct(Subscription $subscription, int $daysUntilExpiration)
    {
        $this->subscription = $subscription;
        $this->daysUntilExpiration = $daysUntilExpiration;
    }
}
