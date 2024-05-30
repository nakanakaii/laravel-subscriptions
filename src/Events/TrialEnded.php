<?php

namespace Nakanakaii\LaravelSubscriptions\Events;

use Nakanakaii\LaravelSubscriptions\Models\Subscription;

/**
 * The TrialEnded event class represents an event that is triggered when a user's trial period ends.
 *
 * This event is typically dispatched after a subscription's trial period expires and the subscription
 * transitions to either an active state (if billing is enabled) or a cancelled state (if no billing
 * information is provided). It allows other parts of your application to react to the trial ending,
 * such as prompting users to upgrade to a paid plan, sending notification emails, or adjusting user
 * permissions.
 */
class TrialEnded
{
    /**
     * The subscription object where the trial has ended.
     *
     * @var Subscription
     */
    public $subscription;

    /**
     * Constructor for the event.
     *
     * This constructor takes an instance of the `Subscription` model representing the subscription where the trial has ended.
     *
     * @param  Subscription  $subscription  The subscription object.
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}
