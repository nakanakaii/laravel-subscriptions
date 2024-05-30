<?php

namespace Nakanakaii\LaravelSubscriptions\Policies;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;
use Nakanakaii\LaravelSubscriptions\Models\Feature;

class UserPolicy
{
    public function hasFeature(User $user, string $feature_key, ?string $model = null): Response
    {
        $feature = Feature::where('key', $feature_key)->first();

        if (! $user->subscription) {
            return Response()::deny('You do not have an active subscription.');
        }

        if ($user->subscription->isExpired()) {
            return Response()::deny('You cannot access this feature.');
        }

        if (! $user->subscription->plan->features->contains($feature)) {
            return Response()::deny('You do not have access to this feature.');
        }
        $plan_feature = $user->subscription->plan->features->where('feature_id', $feature->id)->first();

        if (! $plan_feature->is_enabled) {
            return Response()::deny('You do not have access to this feature.');
        }

        if ($model != null) {
            if (! class_exists($model)) {
                return Response()::deny("No model $model found");
            }

            if ($plan_feature->model == $model) {
                $class = new $model();

                $count = $class->where('user_id', $user->id)->count();

                if ($count >= $plan_feature->value) {
                    return Response()::deny('You have reached your limit for this feature.');
                }
            }
        }

        return Response()::allow();
    }

    public function isActive(User $user)
    {
        return $user->subscription && $user->subscription->isActive() ? Response::allow() : Response::deny('You do not have an active subscription.');
    }

    public function canCancel(User $user)
    {

        if (! $user->subscription) {
            return Response::deny('You do not have an active subscription.');
        }

        if (! $user->subscription->onGracePeriod()) {
            return Response::deny('You cannot cancel your subscription.');
        }

        return Response::allow();
    }

    public function viewInvoices(User $user)
    {
        if (! $user->subscription) {
            return Response::deny('You do not have an active subscription.');
        }

        if (! $user->subscription->invoices->count() > 0) {
            return Response::deny('You do not have any invoices.');
        }

        return Response::allow();
    }
}
