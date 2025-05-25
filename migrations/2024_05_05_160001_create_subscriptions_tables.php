<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('yearly_price', 10, 2)->nullable();
            $table->decimal('monthly_first_time_discount', 10, 2)->nullable();
            $table->decimal('yearly_first_time_discount', 10, 2)->nullable();
            $table->string('currency');
            $table->integer('trial_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('limits')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['plan_id', 'feature_id']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs('subscriber');
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'trial', 'pending', 'cancelled', 'expired'])->default('active');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->boolean('auto_renew')->default(false);
            $table->boolean('is_active')->default(false);
            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('started_at');
            $table->dateTime('ends_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->references('id')->on('subscriptions');
            $table->string('invoice_id')->nullable()->unique();
            $table->enum('status', ['pending', 'paid', 'failed', 'overdue'])->default('pending');
            $table->json('files')->nullable();
            $table->string('currency');
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('taxes', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->text('description');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('features');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_invoices');
    }
};
