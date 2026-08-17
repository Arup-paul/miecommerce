<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    protected AddressService $addresses;

    public function __construct(AddressService $addresses)
    {
        $this->addresses = $addresses;
    }

    public function index()
    {
        $userId = Auth::id();
        $addresses = Address::where('user_id', $userId)->orderByDesc('id')->get();

        return view('addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('addresses.create');
    }

    public function store(Request $request)
    {
        $validator = $this->validateRule($request);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();

        Address::create($data);

        return redirect()->route('addresses.index')->with('status', 'Address created.');
    }

    public function edit(Address $address)
    {
        if (! $this->ownedByCurrentUser($address)) {
            abort(403);
        }

        return view('addresses.edit', compact('address'));
    }

    public function update(Request $request, Address $address)
    {
        if (! $this->ownedByCurrentUser($address)) {
            abort(403);
        }

        $validator = $this->validateRule($request);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $address->update($validator->validated());

        return redirect()->route('addresses.index')->with('status', 'Address updated.');
    }

    public function destroy(Address $address)
    {
        if (! $this->ownedByCurrentUser($address)) {
            abort(403);
        }

        $address->delete();

        return redirect()->route('addresses.index')->with('status', 'Address deleted.');
    }

    /**
     * Mark the given address as the user's default billing or shipping address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Address  $address
     */
    public function setDefault(Request $request, Address $address)
    {
        if (! $this->ownedByCurrentUser($address)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'kind' => ['required', 'in:billing,shipping'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $kind = $request->get('kind');
        $this->addresses->setDefault($address, $kind);

        return redirect()->route('addresses.index')->with('status', 'Default address updated.');
    }

    /**
     * Validate a saved address submission.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function validateRule(Request $request)
    {
        return Validator::make($request->all(), [
            'label' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'area' => ['nullable', 'string', 'max:120'],
        ]);
    }

    /**
     * Whether the given address belongs to the authenticated user.
     *
     * @param  \App\Models\Address  $address
     */
    protected function ownedByCurrentUser(Address $address)
    {
        if (empty($address->user_id)) {
            return false;
        }

        return $address->user_id === Auth::id();
    }
}
