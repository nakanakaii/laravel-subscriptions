<?php

namespace Nakanakaii\LaravelSubscriptions\Events;

use Illuminate\Database\Eloquent\Model;

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
     * The model object who unsubscribed.
     *
     * @var model
     */
    public $model;

    /**
     * Constructor for the event.
     *
     * This constructor takes an instance of the model representing who unsubscribed.
     *
     * @param  Model  $model  The model object.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
