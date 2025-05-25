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
    protected static function subscriber()
    {
        return Auth::user();
    }

    public static function subscribe(SubscriptionRequest $request): RedirectResponse
    {
        $request->validated();

        $subscriber = self::subscriber();
        $plan = Plan::findOrFail($request->plan_id);

        if ($subscriber->subscription) {
            $subscriber->subscription->delete(); // remove any old subscription
        }

        $billingDays = $request->billing_cycle === 'yearly' ? 365 : 30;
        $trialEndsAt = now()->addDays($plan->trial_days ?? 0);

        $subscription = $subscriber->subscription()->create([
            'plan_id' => $plan->id,
            'status' => $plan->trial_days ? Subscription::STATUS_TRIAL : Subscription::STATUS_ACTIVE,
            'billing_cycle' => $request->billing_cycle,
            'trial_ends_at' => $plan->trial_days ? $trialEndsAt : null,
            'started_at' => $plan->trial_days ? $trialEndsAt : now(),
            'ends_at' => $plan->trial_days
                ? $trialEndsAt->copy()->addDays($billingDays)
                : now()->addDays($billingDays),
        ]);

        $subscription->recordInvoice(
            amount: $plan->price,
            description: "{$plan->name} ({$request->billing_cycle})"
        );

        event(new Subscribed($subscriber));

        return back()->with('success', 'You have successfully subscribed to the plan.');
    }

    public static function renew(SubscriptionRenewalRequest $request): RedirectResponse
    {
        $subscriber = self::subscriber();
        $subscription = $subscriber->subscription;

        if (! $subscription) {
            return back()->with('error', 'You do not have an active subscription to renew.');
        }

        $billingDays = $request->annual ? 365 : 30;

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => $subscription->ends_at->copy()->addDays($billingDays),
        ]);

        return back()->with('success', 'Your subscription has been renewed.');
    }

    public static function cancel(): RedirectResponse
    {
        $subscriber = self::subscriber();

        if (Gate::denies('canCancel', $subscriber)) {
            return back()->with('error', 'You are not authorized to cancel this subscription.');
        }

        $subscriber->subscription->cancel();

        event(new Unsubscribed($subscriber));

        return back()->with('success', 'Your subscription has been cancelled.');
    }

    public static function resume(): RedirectResponse
    {
        $subscriber = self::subscriber();

        if (! $subscriber->subscription || ! $subscriber->subscription->isCancelled()) {
            return back()->with('error', 'No cancelled subscription to resume.');
        }

        $subscriber->subscription->activate();

        return back()->with('success', 'Your subscription has been resumed.');
    }
}
