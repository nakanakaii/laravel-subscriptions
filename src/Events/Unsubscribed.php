<?php

namespace Nakanakaii\LaravelSubscriptions\Events;

use Illuminate\Foundation\Auth\User;

/**
 * The Unsubscribed event class represents an event that is triggered when a user unsubscribes from the system.
 *
 * This event is typically dispatched after a user's subscription is cancelled. It allows other parts
 * of your application to react to the user unsubscribing, such as sending feedback emails, removing user
 * access, or archiving user data.
 */
class Unsubscribed
{
    /**
     * The user object who unsubscribed.
     *
     * @var User
     */
    public $user;

    /**
     * Constructor for the event.
     *
     * This constructor takes an instance of the `User` model representing the user who unsubscribed.
     *
     * @param  User  $user  The user object.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
