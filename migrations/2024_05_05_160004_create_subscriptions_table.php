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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            if (config('subscriptions.use_teams', false)) {
                $table->foreignId('team_id')->constrained()->references('id')->on('teams');
            } else {
                $table->foreignId('user_id')->constrained()->references('id')->on('users');
            }
            $table->foreign('plan_id')->constrained()->references('id')->on('plans');
            $table->enum('status', ['active', 'trial', 'pending', 'cancelled', 'expired'])->default('active');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->boolean('auto_renew')->default(false);
            $table->boolean('is_active')->default(false);
            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
