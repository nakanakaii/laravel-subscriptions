<?php

namespace Nakanakaii\LaravelSubscriptions;

use Illuminate\Support\Facades\Route;

class Subscriptions
{
    public static function routes()
    {
        return Route::group(
            [],
            function () {
                Route::controller('App\\Http\\Controllers\\SubscriptionController')->prefix('subscriptions')->group(function () {
                    Route::get('/', 'index')->name('subscriptions.index');
                    Route::get('/{id}', 'show')->name('subscriptions.show');
                    Route::post('/{id}/subscribe', 'subscribe')->name('subscriptions.subscribe');
                    Route::post('/{id}/renew', 'renew')->name('subscriptions.renew');
                    Route::post('/{id}/cancel', 'cancel')->name('subscriptions.cancel');
                    Route::post('/{id}/resume', 'resume')->name('subscriptions.resume');
                });

                Route::controller('App\\Http\\Controllers\\PlanController')->prefix('plans')->group(function () {
                    Route::get('/', 'index')->name('plans.index');
                    Route::get('/create', 'showCreateForm')->name('plans.showCreateForm');
                    Route::post('/create', 'create')->name('plans.create');
                    Route::get('/{id}', 'showPlan')->name('plans.show');
                    Route::get('/{id}/update', 'showUpdateForm')->name('plans.showUpdateForm');
                    Route::post('/{id}/update', 'update')->name('plans.update');
                    Route::post('/{id}/delete', 'delete')->name('plans.delete');
                });

                Route::controller('App\\Http\\Controllers\\FeatureController')->prefix('features')->group(function () {
                    Route::get('/', 'index')->name('features.index');
                    Route::get('/create', 'showCreateForm')->name('features.showCreateForm');
                    Route::post('/create', 'create')->name('features.create');
                    Route::get('/{id}', 'showFeature')->name('features.show');
                    Route::get('/{id}/update', 'showUpdateForm')->name('features.showUpdateForm');
                    Route::post('/{id}/update', 'update')->name('features.update');
                    Route::post('/{id}/delete', 'delete')->name('features.delete');
                });
            }
        );
    }
}
