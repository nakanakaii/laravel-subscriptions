<?php

namespace Nakanakaii\LaravelSubscriptions\Tests\Models;

use Illuminate\Foundation\Testing\TestCase;
use Nakanakaii\LaravelSubscriptions\Models\Subscription;
use Nakanakaii\LaravelSubscriptions\Models\SubscriptionInvoice;

class SubscriptionInvoiceTest extends TestCase
{
    public function test_create_new_invoice()
    {
        $subscription = Subscription::create();
        $invoice = new SubscriptionInvoice([
            'subscription_id' => $subscription->id,
            'amount' => 100,
            'description' => 'Monthly subscription fee',
        ]);
        $invoice->save();

        $this->assertDatabaseHas('subscription_invoices', [
            'subscription_id' => $subscription->id,
            'amount' => 100,
            'description' => 'Monthly subscription fee',
        ]);
    }

    public function test_get_associated_subscription()
    {
        $subscription = Subscription::create()->invoices()->create(
            [
                'amount' => 100,
                'description' => 'Monthly subscription fee',
            ]
        );

        $this->assertTrue($subscription->invoices->first());
    }
}
