# Laravel Subscriptions

A fully-featured, multi-tenant-compatible Laravel package for managing subscription plans, features, usage limits, invoices, and access control — designed to work with any polymorphic subscriber model (`User`, `Team`, `Organization`, etc.).

## 🚀 Features

- Multi-tenancy and single-user compatible
- Polymorphic subscription support
- Monthly & yearly billing cycles
- Trial periods and first-time discounts
- Plan-based feature toggles with usage limits
- Subscription invoices with taxes and discounts
- Full policy support for access control
- Clean database schema with soft deletes

## 📦 Installation

```bash
composer require nakanakaii/laravel-subscriptions
```

## 🔧 Setup

### 1. Publish Migrations and config, and migrate your DB

```bash
php artisan vendor:publish --tag="subscriptions-migrations"
php artisan vendor:publish --tag="subscriptions-config"
php artisan migrate
```

### 2. Add the `HasSubscription` Trait

In your subscriber model (e.g., `User`, `Team`, etc.):

```php
use Nakanakaii\LaravelSubscriptions\Traits\HasSubscription;

class User extends Authenticatable
{
    use HasSubscription;

    // ...
}
```

### 3. Define Policies (optional but recommended)

Register the `SubscriptionPolicy`:

```php
use Nakanakaii\LaravelSubscriptions\Models\Subscription;
use Nakanakaii\LaravelSubscriptions\Policies\SubscriptionPolicy;

Gate::policy(Subscription::class, SubscriptionPolicy::class);
```

Or define them in your `AuthServiceProvider`.

---

## 🧠 Usage

### Create a Plan with Features

```php
$plan = Plan::create([
    'name' => 'Pro Plan',
    'monthly_price' => 19.99,
    'currency' => 'USD',
    'trial_days' => 14,
]);

$feature = Feature::create([
    'name' => 'API Requests/month',
    'key' => 'api_requests',
]);

$plan->features()->attach($feature->id, [
    'limits' => json_encode(['max' => 10000]),
    'is_enabled' => true,
]);
```

### Assign Subscription to a User

```php
$user->subscription()->create([
    'plan_id' => $plan->id,
    'status' => 'active',
    'billing_cycle' => 'monthly',
    'started_at' => now(),
    'ends_at' => now()->addMonth(),
]);
```

### Check if Subscriber Has Feature

```php
if (Gate::forUser($user)->allows('hasFeature', ['api_requests'])) {
    // Feature access granted
}
```

Or via policy call:

```php
SubscriptionPolicy::hasFeature($user, 'api_requests');
```

---

## 📄 Policies Available

* `hasFeature($subscriber, $featureKey, $model)`
* `isActive($subscriber)`
* `isOnTrial($subscriber)`
* `canCancel($subscriber)`
* `canRenew($subscriber)`
* `canChangePlan($subscriber)`
* `viewInvoices($subscriber)`
* `canDownloadInvoice($subscriber, $invoiceId)`
* `canViewFeatureLimits($subscriber, $featureKey)`

---

## 📚 Models Overview

| Model                 | Description                                               |
| --------------------- | --------------------------------------------------------- |
| `Plan`                | Holds plan data like name, price, trial, etc.             |
| `Feature`             | Defines access-controlled features                        |
| `PlanFeature`         | Pivot table storing plan-feature relation, limits, status |
| `Subscription`        | The actual subscription tied to a subscriber              |
| `SubscriptionInvoice` | Stores billing info, amount, tax, discount                |

---

## 🏗️ Customization

* Extend the `SubscriptionPolicy` for more complex business logic
* Modify `plan_features.limits` structure to use tiered, time-based, or usage patterns
* Customize invoice creation during billing events

---

## 📜 License

MIT © [Nakanakaii](https://github.com/nakanakaii)

