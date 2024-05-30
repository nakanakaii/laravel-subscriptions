<?php

namespace Nakanakaii\LaravelSubscriptions\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Nakanakaii\LaravelSubscriptions\Models\Plan;

trait PlanTrait
{
    /**
     * Creates a new plan in the system.
     *
     * This method takes an `Illuminate\Http\Request` object containing information about the new plan.
     * It extracts the following details from the request:
     *  - Name of the plan
     *  - Description of the plan
     *  - Monthly price of the plan
     *  - Yearly price of the plan (optional)
     *  - First-time discount offered (optional)
     *  - Currency used for pricing
     *  - Trial period duration in days (optional)
     *  - Activation status of the plan
     *
     * It then uses `Plan::create` to create a new plan record in the database with the provided data.
     * Finally, it returns a redirect response with a success message and the newly created plan object.
     *
     * @param  Request  $request  The request containing the plan data
     */
    public function create(Request $request): RedirectResponse
    {
        $plan = Plan::create([
            'name' => $request->name,
            'description' => $request->description,
            'monthly_price' => $request->monthly_price,
            'yearly_price' => $request->yearly_price,
            'first_time_discount' => $request->first_time_discount,
            'currency' => $request->currency,
            'trial_days' => $request->trial_days,
            'is_active' => $request->is_active,
        ]);

        return back()
            ->with('success', 'Plan created successfully')
            ->with('plan', $plan);
    }

    /**
     * Updates an existing plan in the system.
     *
     * This method takes an `Illuminate\Http\Request` object containing updated information for a plan
     * and the ID of the plan to be updated. It performs the following actions:
     *  - Finds the plan using `Plan::find($plan_id)`.
     *  - Checks if the plan has any associated features (likely a relationship with another model).
     *  - If there are no associated features, it updates the plan details using `update` with the provided data from the request.
     *  - If there are associated features, it updates the plan details and then synchronizes the associated features
     *     using `sync` based on the features provided in the request.
     * - Finally, it returns a redirect response with a success message.
     *
     * @param  Request  $request  The request containing the plan data
     * @param  int  $plan_id  The ID of the plan to update
     * @return RedirectResponse Returns a redirect response with a success message
     */
    public function update(Request $request, int $plan_id): RedirectResponse
    {
        $plan = Plan::find($plan_id);
        if ($plan->features()->count() > 0) {
            $plan->update([
                'name' => $request->name,
                'description' => $request->description,
                'monthly_price' => $request->monthly_price,
                'yearly_price' => $request->yearly_price,
                'first_time_discount' => $request->first_time_discount,
                'currency' => $request->currency,
                'trial_days' => $request->trial_days,
                'is_active' => $request->is_active,
            ]);
        }

        if ($plan->features()->count() > 0) {
            $plan->features()->sync($request->features);
        }

        return back()->with('success', 'Plan updated successfully');
    }

    /**
     * Deletes a plan from the system.
     *
     * This method takes the ID of the plan to be deleted. It uses `Plan::find($plan_id)` to retrieve the plan
     * and then deletes it using `delete`. Finally, it returns a redirect response with a success message.
     *
     * @param  int  $plan_id  The ID of the plan to delete
     * @return RedirectResponse Returns a redirect response with a success message
     */
    public function destroy(int $plan_id): RedirectResponse
    {
        Plan::find($plan_id)->delete();

        return back()->with('success', 'Plan deleted successfully');
    }
}
