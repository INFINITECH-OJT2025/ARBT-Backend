<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::all();
        return response()->json($plans);
    }


    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        // ✅ Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'features' => 'required', // Remove 'json' rule to prevent validation failure
        ]);

        // ✅ Ensure features is stored as JSON
        $plan->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'description' => $validated['description'],
            'features' => is_string($validated['features']) ? $validated['features'] : json_encode($validated['features']),
        ]);

        return response()->json($plan);
    }

}
