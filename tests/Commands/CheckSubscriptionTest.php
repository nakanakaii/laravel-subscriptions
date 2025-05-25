<?php

namespace Nakanakaii\LaravelSubscriptions\Tests\Models;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Event;
use Nakanakaii\LaravelSubscriptions\Commands\CheckSubscription;
use Nakanakaii\LaravelSubscriptions\Events\SubscriptionExpired;
use Nakanakaii\LaravelSubscriptions\Events\TrialEnded;
use Nakanakaii\LaravelSubscriptions\Models\Subscription;

class CheckSubscriptionTest extends TestCase
{
    public function test_check_for_trial_end()
    {
        Event::fake();

        $subscription = Subscription::create([
            'trial_ends_at' => now()->subDay(),
        ]);

        $command = new CheckSubscription();
        $command->handle();

        $this->assertEquals(Subscription::STATUS_PENDING, $subscription->fresh()->status);
        $this->assertTrue(Event::assertDispatched(
            TrialEnded::class,
            function ($event) use ($subscription) {
                return $event->subscription->id === $subscription->id;
            }
        ));
    }

    public function test_check_for_subscription_expiration()
    {
        Event::fake();

        $subscription = Subscription::create([
            'ends_at' => now()->subDay(),
        ]);

        $command = new CheckSubscription();
        $command->updateSubscriptionStatus($subscription);

        $this->assertEquals(Subscription::STATUS_EXPIRED, $subscription->fresh()->status);
        $this->assertTrue(Event::assertDispatched(
            SubscriptionExpired::class,
            function ($event) use ($subscription) {
                return $event->subscription->id === $subscription->id;
            }
        ));
    }

    public function test_handle_active_subscriptions()
    {
        Event::fake();

        $subscription = Subscription::create([
            'ends_at' => now()->addDay(),
        ]);

        $command = new CheckSubscription();
        $command->updateSubscriptionStatus($subscription);

        $this->assertEquals(Subscription::STATUS_ACTIVE, $subscription->fresh()->status);
        $this->assertFalse(Event::assertDispatched(
            TrialEnded::class,
            function ($event) use ($subscription) {
                return $event->subscription->id === $subscription->id;
            }
        ));
        $this->assertFalse(Event::assertDispatched(
            SubscriptionExpired::class,
            function ($event) use ($subscription) {
                return $event->subscription->id === $subscription->id;
            }
        ));
    }

    public function test_handle_subscriptions_with_no_end_date()
    {
        Event::fake();

        $subscription = Subscription::create([
            'ends_at' => null,
        ]);

        $command = new CheckSubscription();
        $command->updateSubscriptionStatus($subscription);

        $this->assertEquals(Subscription::STATUS_ACTIVE, $subscription->fresh()->status);
        $this->assertFalse(Event::assertDispatched(
            TrialEnded::class,
            function ($event) use ($subscription) {
                return $event->subscription->id === $subscription->id;
            }
        ));
        $this->assertFalse(Event::assertDispatched(
            SubscriptionExpired::class,
            function ($event) use ($subscription) {
                return $event->subscription->id === $subscription->id;
            }
        ));
    }

    public function test_handle_subscriptions_with_no_trial_end_date()
    {
        $subscription = Subscription::create([
            'trial_ends_at' => null,
        ]);

        $command = new CheckSubscription();
        $command->updateSubscriptionStatus($subscription);

        $this->assertEquals(Subscription::STATUS_ACTIVE, $subscription->fresh()->status);
        $this->assertFalse(Event::assertDispatched(
            TrialEnded::class,
            function ($event) use ($subscription) {
                return $event->subscription->id === $subscription->id;
            }
        ));
        $this->assertFalse(Event::assertDispatched(
            SubscriptionExpired::class,
            function ($event) use ($subscription) {
                return $event->subscription->id === $subscription->id;
            }
        ));
    }
}
