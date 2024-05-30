<?php

namespace Nakanakaii\LaravelSubscriptions\Commands;

use Illuminate\Console\Command;
use Nakanakaii\LaravelSubscriptions\Events\SubscriptionExpired;
use Nakanakaii\LaravelSubscriptions\Events\SubscriptionWarning;
use Nakanakaii\LaravelSubscriptions\Events\TrialEnded;
use Nakanakaii\LaravelSubscriptions\Models\Subscription;

/**
 * The CheckSubscription command is a Laravel Artisan console command used to periodically (usually using cron jobs) check and update subscription statuses.
 *
 * This command is designed to be run as a scheduled task to ensure subscriptions are kept up-to-date based on their trial
 * periods and end dates. It iterates through all subscriptions in the system and performs the following actions:
 *
 *  1. Checks for Trial End:
 *     - It verifies if the subscription is currently in a trial period using the `isTrial` method on the Subscription model.
 *     - It compares the subscription's `trial_ends_at` property with the current date and time using `now()->gte`.
 *     - If both conditions are true (trial active and end date has passed), it performs the following actions:
 *          1. Updates the subscription status to `Subscription::STATUS_PENDING`, indicating the need for payment information.
 *          2. Saves the updated subscription model using `save`.
 *          3. Dispatches a `TrialEnded` event with the subscription object, allowing other parts of your application to react.
 *          4. Logs an informational message indicating the trial end for the specific subscription ID.
 *  2. Checks for Subscription Expiration:
 *     - It verifies if the subscription is currently active using the `isActive` method on the Subscription model.
 *     - It compares the subscription's `ends_at` property with the current date and time using `now()->gte`.
 *     - If both conditions are true (subscription active and end date has passed), it performs the following actions:
 *          1. Updates the subscription status to `Subscription::STATUS_EXPIRED`.
 *          2. Saves the updated subscription model using `save`.
 *          3. Dispatches a `SubscriptionExpired` event with the subscription object, allowing other parts of your application to react.
 *          4. Logs an informational message indicating the expiration for the specific subscription ID.
 */
class CheckSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks subscription statuses and updates them based on trial periods and end dates.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $subscriptions = Subscription::all();

        foreach ($subscriptions as $subscription) {
            $this->updateSubscriptionStatus($subscription);
        }

        $this->info('Subscription statuses have been checked and updated.');

        return 0;
    }

    private function updateSubscriptionStatus(Subscription $subscription)
    {

        if ($subscription->isTrial() && now()->gte($subscription->trial_ends_at)) {
            $subscription->status = Subscription::STATUS_PENDING;
            $subscription->save();

            event(new TrialEnded($subscription));
            $this->info('Trial period has ended for subscription: '.$subscription->id);

            // Optionally fire an event or send notification
        } elseif ($subscription->isActive() && now()->gte($subscription->ends_at)) {
            $subscription->status = Subscription::STATUS_EXPIRED;
            $subscription->save();

            event(new SubscriptionExpired($subscription));
            $this->info('Subscription has expired for subscription: '.$subscription->id);
        } else {
            $daysUntilRenewal = $subscription->ends_at->diffInDays(now());
            if (($subscription->isYearly() && $daysUntilRenewal <= 30) || ($subscription->isMonthly() && $daysUntilRenewal <= 7)) {
                event(new SubscriptionWarning($subscription, $daysUntilRenewal));
                $this->info('Subscription nearing expiration ('.($subscription->isYearly() ? 'yearly' : 'monthly').') for subscription: '.$subscription->id);
            }
        }

    }
}
