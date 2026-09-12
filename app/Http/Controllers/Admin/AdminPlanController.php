<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPlanController extends Controller {
    public function index() {
        $plans = Plan::orderBy('sort_order')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function create() {
        return view('admin.plans.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly,one_time,custom',
            'duration_days' => 'nullable|integer|min:1',
            'credits_included' => 'required|integer|min:0',
            'max_users' => 'required|integer|min:1',
            'max_searches_per_day' => 'required|integer|min:1',
            'max_data_units_total' => 'nullable|integer|min:1',
        ]);

        Plan::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'billing_cycle' => $request->billing_cycle,
            'duration_days' => $request->duration_days ?: ($request->billing_cycle === 'custom' ? 28 : null),
            'credits_included' => $request->credits_included,
            'max_users' => $request->max_users,
            'max_searches_per_day' => $request->max_searches_per_day,
            'max_data_units_total' => $request->max_data_units_total,
            'reset_daily_limit_on_period' => $request->boolean('reset_daily_limit_on_period', false),
            'features' => $request->features ? json_decode($request->features, true) : null,
            'is_active' => $request->boolean('is_active', true),
            'is_free' => $request->price == 0,
            'sort_order' => Plan::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully!');
    }

    public function edit($id) {
        $plan = Plan::findOrFail($id);
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, $id) {
        $plan = Plan::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly,one_time,custom',
            'duration_days' => 'nullable|integer|min:1',
            'max_data_units_total' => 'nullable|integer|min:1',
        ]);

        $plan->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'billing_cycle' => $request->billing_cycle,
            'duration_days' => $request->duration_days,
            'credits_included' => $request->credits_included,
            'max_users' => $request->max_users,
            'max_searches_per_day' => $request->max_searches_per_day,
            'max_data_units_total' => $request->max_data_units_total,
            'reset_daily_limit_on_period' => $request->boolean('reset_daily_limit_on_period', false),
            'is_active' => $request->boolean('is_active', true),
            'is_free' => $request->price == 0,
        ]);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully.');
    }
}