<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    /**
     * List coupons for the admin screen.
     */
    public function index()
    {
        $coupons = Coupon::latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the new-coupon form.
     */
    public function create()
    {
        return view('admin.coupons.create');
    }

    /**
     * Persist a new coupon.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $validator = $this->validateRule($request, null);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Coupon::create($validator->validated());

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon created.');
    }

    /**
     * Show the edit form for one coupon.
     *
     * @param  \App\Models\Coupon  $coupon
     */
    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Persist changes to an existing coupon.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coupon  $coupon
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validator = $this->validateRule($request, $coupon);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $coupon->update($validator->validated());

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon updated.');
    }

    /**
     * Delete a coupon.
     *
     * @param  \App\Models\Coupon  $coupon
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon deleted.');
    }

    /**
     * Validate a create/update submission, ignoring the current row's own code on update and
     * capping a percent-type value at 100.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coupon|null  $coupon
     */
    protected function validateRule(Request $request, $coupon)
    {
        $uniqueCode = Rule::unique('coupons', 'code');

        if (! empty($coupon)) {
            $uniqueCode = $uniqueCode->ignore($coupon->id);
        }

        return Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:50', $uniqueCode],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0', $this->valueMaxRule($request)],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    /**
     * The max-value validation rule, capped at 100 only when the submitted type is percent.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function valueMaxRule(Request $request)
    {
        if ($request->input('type') === 'percent') {
            return 'max:100';
        }

        return 'max:999999999.99';
    }
}
