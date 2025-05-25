<?php

namespace Nakanakaii\LaravelSubscriptions\Commands;

use Carbon\Carbon;
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
    protected $signature = 'app:check-subscriptions {--chunk-size=100 : The number of subscriptions to process at a time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks subscription statuses and updates them based on trial periods and end dates, processing in chunks.';

    public function handle()
    {
        $this->info('Starting subscription status check...');

        $chunkSize = (int) $this->option('chunk-size');
        $subscriptionsProcessed = 0;
        $now = Carbon::now(); // Get current time once for consistency within the batch

        // Eager load relationships if any are accessed within the loop (e.g., user, plan)
        // For now, assuming isTrial, isActive, isYearly, isMonthly are simple accessors or scopes
        // that don't trigger N+1 queries for relationships.
        // If they do, add ->with(['relationship1', 'relationship2']) before chunkById.

        // Only select subscriptions that might need updating to reduce the initial dataset
        Subscription::query()
            ->where(function ($query) use ($now) {
                // Trials that might have ended or are ending
                $query->where('status', Subscription::STATUS_TRIAL) // Assuming you have a STATUS_TRIAL constant
                    ->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '<=', $now);
            })
            ->orWhere(function ($query) use ($now) {
                // Active subscriptions that might have expired or are nearing expiration
                $query->where('status', Subscription::STATUS_ACTIVE) // Assuming you have a STATUS_ACTIVE constant
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '<=', $now->copy()->addDays(30)); // Check up to 30 days in advance for warnings
            })
            ->chunkById($chunkSize, function ($subscriptions) use (&$subscriptionsProcessed, $now) {
                if ($subscriptions->isEmpty()) {
                    return false; // Stop chunking if no subscriptions are found in a chunk
                }

                foreach ($subscriptions as $subscription) {
                    $this->updateSubscriptionStatus($subscription, $now);
                    $subscriptionsProcessed++;
                }

                // Optional: Add a small delay to reduce server load if processing many chunks
                // usleep(100000); // 100ms delay

                // Optional: Log progress for long-running tasks
                // $this->comment("Processed {$subscriptionsProcessed} subscriptions so far...");
            });

        if ($subscriptionsProcessed > 0) {
            $this->info("Subscription statuses checked and updated for {$subscriptionsProcessed} subscriptions.");
        } else {
            $this->info('No subscriptions required updates at this time.');
        }

        return Command::SUCCESS; // Use Command constants for return codes
    }

    private function updateSubscriptionStatus(Subscription $subscription, Carbon $now)
    {
        $originalStatus = $subscription->status;
        $statusChanged = false;

        // Check for Trial End
        // Ensure trial_ends_at is a Carbon instance or cast it
        $trialEndsAt = $subscription->trial_ends_at instanceof Carbon ? $subscription->trial_ends_at : Carbon::parse($subscription->trial_ends_at);
        if ($subscription->isTrial() && $trialEndsAt && $now->gte($trialEndsAt)) {
            $subscription->status = Subscription::STATUS_PENDING; // Or whatever status signifies trial ended
            $subscription->save();
            event(new TrialEnded($subscription));
            $this->line("<error>Trial ended for subscription:</error> {$subscription->id}");
            $statusChanged = true;
        }
        // Check for Subscription Expiration (only if not already processed as trial ended)s
        // Ensure ends_at is a Carbon instance or cast it
        elseif ($subscription->isActive()) {
            $endsAt = $subscription->ends_at instanceof Carbon ? $subscription->ends_at : Carbon::parse($subscription->ends_at);
            if ($endsAt && $now->gte($endsAt)) {
                $subscription->status = Subscription::STATUS_EXPIRED;
                $subscription->save();
                event(new SubscriptionExpired($subscription));
                $this->line("<error>Subscription expired:</error> {$subscription->id}");
                $statusChanged = true;
            }
            // Check for Renewal Warnings (only if not expired)
            else if ($endsAt) { // Ensure $endsAt is not null
                $daysUntilRenewal = $endsAt->diffInDays($now, false); // Pass false to get signed difference

                // Warning if ends_at is in the future but nearing
                if ($daysUntilRenewal >= 0) { // Ensure it's not already past
                    if (($subscription->isYearly() && $daysUntilRenewal <= 30) ||
                        ($subscription->isMonthly() && $daysUntilRenewal <= 7)
                    ) {
                        // Avoid sending warnings multiple times if the command runs frequently.
                        // You might need a flag on the subscription or a separate table to track if a warning was sent.
                        // For simplicity, this example dispatches every time it meets the criteria.
                        event(new SubscriptionWarning($subscription, $daysUntilRenewal));
                        $this->line("<warning>Subscription nearing expiration warning (" . ($subscription->isYearly() ? 'yearly, ' . $daysUntilRenewal . ' days left' : 'monthly, ' . $daysUntilRenewal . ' days left') . ") for subscription:</warning> {$subscription->id}");
                    }
                }
            }
        }

        // If status hasn't changed to expired or pending from trial end, consider other checks
        if (!$statusChanged && $originalStatus === $subscription->status) {
            // Potentially log that no action was taken for this specific subscription if needed for debugging
            // $this->line("<fg=gray>No status change for subscription:</> {$subscription->id}");
        }
    }
}
