<?php

namespace Nakanakaii\LaravelSubscriptions\Events;

use Illuminate\Foundation\Auth\User;

/**
 * The Subscribed event class represents an event that is triggered when a user subscribes to the system.
 *
 * This event is typically dispatched after a successful subscription creation process. It allows other parts
 * of your application to react to the user's subscription, such as sending welcome emails, logging activity,
 * or updating user profiles.
 */
class Subscribed
{
    /**
     * The user object who subscribed.
     *
     * @var User
     */
    public $user;

    /**
     * Constructor for the event.
     *
     * This constructor takes an instance of the `User` model representing the user who subscribed.
     *
     * @param  User  $user  The user object.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
