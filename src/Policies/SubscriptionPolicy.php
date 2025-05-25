<?php

namespace Nakanakaii\LaravelSubscriptions\Policies;

use Illuminate\Auth\Access\Response;
use Nakanakaii\LaravelSubscriptions\Models\Feature;

class SubscriptionPolicy
{
    protected function hasActiveSubscription($subscriber): bool
    {
        return $subscriber->subscription && $subscriber->subscription->isActive();
    }

    public function hasFeature($subscriber, string $feature_key, ?string $model = null): Response
    {
        $feature = Feature::where('key', $feature_key)->first();

        if (! $feature) {
            return Response::deny("Feature {$feature_key} not found.");
        }

        if (! $this->hasActiveSubscription($subscriber)) {
            return Response::deny('You do not have an active subscription.');
        }

        $plan = $subscriber->subscription->plan;

        $planFeature = $plan->features->where('id', $feature->id)->first();

        if (! $planFeature || ! $planFeature->pivot->is_enabled) {
            return Response::deny('You do not have access to this feature.');
        }

        if ($model && class_exists($model)) {
            $instance = new $model();

            // Optional: replace this with tenant-aware relationship logic
            $count = $instance->where('subscriber_id', $subscriber->id)->count();
            $limit = data_get($planFeature->pivot->limits, 'max');

            if ($limit !== null && $count >= $limit) {
                return Response::deny('You have reached your limit for this feature.');
            }
        }

        return Response::allow();
    }

    public function isActive($subscriber): Response
    {
        return $this->hasActiveSubscription($subscriber)
            ? Response::allow()
            : Response::deny('You do not have an active subscription.');
    }

    public function isOnTrial($subscriber): Response
    {
        if ($subscriber->subscription && $subscriber->subscription->isTrial()) {
            return Response::allow();
        }

        return Response::deny('You are not on a trial subscription.');
    }

    public function canCancel($subscriber): Response
    {
        if (! $subscriber->subscription) {
            return Response::deny('You do not have a subscription.');
        }

        if (! $subscriber->subscription->onGracePeriod()) {
            return Response::deny('You are not within the cancellation grace period.');
        }

        return Response::allow();
    }

    public function canRenew($subscriber): Response
    {
        if (! $subscriber->subscription) {
            return Response::deny('You do not have a subscription.');
        }

        if ($subscriber->subscription->isExpired() || ! $subscriber->subscription->auto_renew) {
            return Response::allow();
        }

        return Response::deny('You cannot renew an already active subscription with auto-renewal.');
    }

    public function canChangePlan($subscriber): Response
    {
        if (! $this->hasActiveSubscription($subscriber)) {
            return Response::deny('You do not have an active subscription to change.');
        }

        return Response::allow();
    }

    public function viewInvoices($subscriber): Response
    {
        if (! $subscriber->subscription || ! $subscriber->subscription->invoices->count()) {
            return Response::deny('You do not have any invoices.');
        }

        return Response::allow();
    }

    public function canDownloadInvoice($subscriber, string $invoiceId): Response
    {
        if (! $subscriber->subscription) {
            return Response::deny('You do not have a subscription.');
        }

        $invoice = $subscriber->subscription->invoices->where('invoice_id', $invoiceId)->first();

        if (! $invoice) {
            return Response::deny('Invoice not found.');
        }

        return Response::allow();
    }

    public function canViewFeatureLimits($subscriber, string $feature_key): Response
    {
        if (! $this->hasActiveSubscription($subscriber)) {
            return Response::deny('You do not have an active subscription.');
        }

        $feature = Feature::where('key', $feature_key)->first();

        if (! $feature) {
            return Response::deny("Feature {$feature_key} not found.");
        }

        $planFeature = $subscriber->subscription->plan->features->where('id', $feature->id)->first();

        if (! $planFeature || ! $planFeature->pivot->is_enabled) {
            return Response::deny('Feature not available in your plan.');
        }

        return Response::allow();
    }
}
