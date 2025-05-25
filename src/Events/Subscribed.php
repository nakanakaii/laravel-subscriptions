<?php

namespace Nakanakaii\LaravelSubscriptions\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * The Subscribed event class represents an event that is triggered when a model subscribes to the system.
 *
 * This event is typically dispatched after a successful subscription creation process. It allows other parts
 * of your application to react to the model's subscription, such as sending welcome emails, logging activity,
 * or updating model profiles.
 */
class Subscribed
{
    /**
     * The model object who subscribed.
     *
     * @var Model
     */
    public $model;

    /**
     * Constructor for the event.
     *
     * This constructor takes an instance of the `Model` model representing who subscribed.
     *
     * @param  Model $model  The model object.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
