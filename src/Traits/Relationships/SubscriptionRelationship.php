<?php

namespace Nakanakaii\LaravelSubscriptions\Traits\Relationships;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Nakanakaii\LaravelSubscriptions\Models\Subscription;

trait SubscriptionRelationship
{
    /**
     * Defines a relationship attribute to be automatically appended to the model's serialized representation.
     *
     * This trait uses the `$append` property to specify that the `subscription` attribute should be included
     * whenever the model is serialized to JSON or an array. This allows for eager loading of the related
     * subscription data.
     *
     * @var array
     */
    protected $append = ['subscription'];

    /**
     * Defines a HasOne relationship with the Subscription model.
     *
     * This method defines a one-to-one relationship between the current model and the `Subscription` model using
     * Laravel's Eloquent ORM. It utilizes the `hasOne` method to establish this connection.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Accessor method for the subscription attribute.
     *
     * This method acts as an accessor for the `subscription` attribute. It simply retrieves the related
     * Subscription model using the `subscription` relationship defined earlier.
     */
    public function getSubscriptionAttribute(): ?Subscription
    {
        return $this->subscription;
    }
}
