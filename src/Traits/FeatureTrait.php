<?php

namespace Nakanakaii\LaravelSubscriptions\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Nakanakaii\LaravelSubscriptions\Models\Feature;

trait FeatureTrait
{
    /**
     * Creates a new feature in the system.
     *
     * This method takes an `Illuminate\Http\Request` object containing information about the new feature.
     * It assumes that all the request data corresponds to the feature model and uses `Feature::create` to create
     * a new record in the database with the provided data.
     * Finally, it returns a redirect response with a success message and the newly created feature object.
     *
     * @param  Request  $request  The request containing the feature data
     */
    public function create(Request $request): RedirectResponse
    {
        $feature = Feature::create($request->all());

        return back()->with('success', 'Feature created successfully')->with('feature', $feature);
    }

    /**
     * Updates an existing feature in the system.
     *
     * This method takes an `Illuminate\Http\Request` object containing updated information for a feature
     * and the ID of the feature to be updated. It performs the following actions:
     *  - Finds the feature using `Feature::find($id)`.
     *  - Updates the feature details using `update` with all the data from the request.
     * - Finally, it returns a redirect response with a success message.
     *
     * @param  Request  $request  The request containing the updated feature data
     * @param  int  $id  The ID of the feature to update
     * @return RedirectResponse Returns a redirect response with a success message
     */
    public function update(Request $request, $id)
    {
        $feature = Feature::find($id);

        $feature->update($request->all());

        return back()->with('success', 'Feature updated successfully');
    }

    /**
     * Deletes a feature from the system.
     *
     * This method takes the ID of the feature to be deleted. It uses `Feature::find($id)` to retrieve the feature
     * and then deletes it using `delete`. Finally, it returns a redirect response with a success message.
     *
     * @param  int  $id  The ID of the feature to delete
     * @return RedirectResponse Returns a redirect response with a success message
     */
    public function destroy($id)
    {
        Feature::find($id)->delete();

        return back()->with('success', 'Feature deleted successfully');
    }
}
