<?php

namespace Nakanakaii\LaravelSubscriptions\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Nakanakaii\LaravelSubscriptions\Events\Subscribed;
use Nakanakaii\LaravelSubscriptions\Events\Unsubscribed;
use Nakanakaii\LaravelSubscriptions\Models\Plan;
use Nakanakaii\LaravelSubscriptions\Models\Subscription;
use Nakanakaii\LaravelSubscriptions\Requests\SubscriptionRenewalRequest;
use Nakanakaii\LaravelSubscriptions\Requests\SubscriptionRequest;

trait SubscriptionTrait
{
    /**
     * Subscribes the currently authenticated user to a plan.
     *
     * This method takes a `SubscriptionRequest` object as input, which contains the plan ID and billing cycle information.
     * It validates the request data and then performs the following actions:
     *  - Retrieves the authenticated user using `Auth::user()`.
     *  - Finds the plan based on the provided ID using `Plan::find($request->plan_id)`.
     *  - Checks if the plan offers a trial period based on `$plan->trial_days`.
     *     - If there's a trial:
     *         1. Creates a new subscription for the user with `Subscription::STATUS_TRIAL` status.
     *         2. Sets the trial end date (`trial_ends_at`) to `now()` plus the trial days.
     *         3. Sets the subscription start date (`started_at`) to the trial end date.
     *         4. Sets the subscription end date (`ended_at`) based on the billing cycle (yearly or monthly).
     *         5. Records an invoice for the plan price and name using `recordInvoice` method.
     *     - If no trial:
     *         1. Creates a new subscription for the user with `Subscription::STATUS_ACTIVE` status.
     *         2. Sets the subscription start date (`started_at`) to `now()`.
     *         3. Sets the subscription end date (`ended_at`) based on the billing cycle (yearly or monthly).
     *         4. Records an invoice for the plan price and name using `recordInvoice` method.
     * - Dispatches a `Subscribed` event with the subscribed user.
     * - Returns a redirect response with a success message.
     *
     * @param  SubscriptionRequest  $request  The request object containing the plan ID and billing cycle.
     * @return RedirectResponse The redirect response with a success message.
     */
    public static function subscribe(SubscriptionRequest $request): RedirectResponse
    {
        $request->validated();

        $user = Auth::user();
        $plan = Plan::find($request->plan_id);
        $trialDays = $plan->trial_days;

        if ($trialDays) {
            $user->subscription()->create([
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_TRIAL,
                'billing_cycle' => $request->billing_cycle,
                'trial_ends_at' => now()->addDays($trialDays),
                'started_at' => now()->addDays($trialDays),
                'ended_at' => now()->addDays($request->billing_cycle == 'yearly' ? 365 : 30),
            ])->recordInvoice($plan->price, $plan->name);
        } else {
            $user->subscription()->create([
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_ACTIVE,
                'started_at' => now(),
                'ended_at' => now()->addDays($request->billing_cycle == 'yearly' ? 365 : 30),
            ])->recordInvoice($plan->price, $plan->name);
        }

        event(new Subscribed($user));

        return back()->with('success', 'Subscribed successfully');
    }

    /**
     * Renews a subscription for the user based on the provided request.
     *
     * This method takes a `SubscriptionRenewalRequest` object as input, which contains information
     * needed for subscription renewal. It performs the following actions:
     *  - Retrieves the authenticated user using `Auth::user()`.
     *  - Retrieves the user's existing subscription using `$user->subscription`.
     *  - Checks if the user has an existing subscription using `isset($subscription)`.
     *     - If there's no subscription, returns a redirect response with an error message.
     *  - Updates the existing subscription with:
     *     1. `Subscription::STATUS_ACTIVE` status.
     *     2. The end date (`ended_at`) extended based on the requested renewal period (yearly or monthly).
     * - Returns a redirect response with a success message.
     *
     * @param  SubscriptionRenewalRequest  $request  The request object containing the information needed for subscription renewal.
     * @return RedirectResponse The redirect response with success or error message based on the renewal outcome.
     */
    public static function renew(SubscriptionRenewalRequest $request)
    {
        $user = Auth::user();
        $subscription = $user->subscription;

        if (! isset($subscription)) {
            return back()->with('error', '');
        }

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'ended_at' => $subscription->ended_at->addDays($request->annual ? 365 : 30),
        ]);

        return back()->with('success', '');
    }

    /**
     * Cancels the user's subscription and triggers the Unsubscribed event.
     *
     * This method performs the following actions:
     *  - Retrieves the authenticated user using `Auth::user()`.
     *  - Checks if the user is authorized to cancel the subscription using `Gate::denies('canCancel', $user)`.
     *     - If the user is not authorized, returns a redirect response with an error message.
     *  - Updates the user's subscription with `Subscription::STATUS_CANCELLED` status.
     *  - Dispatches an `Unsubscribed` event with the unsubscribed user.
     * - Returns a redirect response with a success message indicating cancellation.
     *
     * @return RedirectResponse The redirect response with a success or error message.
     */
    public static function cancel()
    {
        $user = Auth::user();

        if (Gate::denies('canCancel', $user)) {
            return back()->with('error', '');
        }

        $user->subscription->cancel();

        event(new Unsubscribed($user));

        return back()->with('success', 'Subscription cancelled');
    }

    /** Resumes the user's subscription.
     *
     * This method performs the following actions:
     *  - Retrieves the authenticated user using `Auth::user()`.
     *  - Resumes the user's subscription.
     *  - Returns a redirect response with a success message.
     *
     * @return RedirectResponse The redirect response with a success or error message.
     */
    public static function resume()
    {
        $user = Auth::user();
        $user->subscription->activate();

        return back()->with('success', 'Subscription resumed');
    }
}
