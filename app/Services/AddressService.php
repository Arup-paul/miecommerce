<?php

namespace App\Services;

use App\Models\Address;
use Illuminate\Validation\ValidationException;

class AddressService
{
    /**
     * Resolve the billing and shipping address rows for a checkout submission.
     *
     * @param  array  $data
     * @param  int|null  $userId
     */
    public function resolveForCheckout(array $data, $userId)
    {
        if (empty($userId)) {
            return ['billing' => null, 'shipping' => null];
        }

        $billing = $this->resolveSide($data, $userId, 'billing');

        if (! empty($data['shipping_same_as_billing'])) {
            return ['billing' => $billing, 'shipping' => $billing];
        }

        $shipping = $this->resolveSide($data, $userId, 'shipping');

        return ['billing' => $billing, 'shipping' => $shipping];
    }

    /**
     * Resolve one side of the checkout address pair, either a saved row or a new one-off row.
     *
     * @param  array  $data
     * @param  int  $userId
     * @param  string  $side
     */
    protected function resolveSide(array $data, int $userId, string $side)
    {
        $addressId = $data[$side . '_address_id'] ?? null;

        if (! empty($addressId)) {
            return $this->ownedAddress((int) $addressId, $userId);
        }

        return $this->createOneOff($data, $userId, $side);
    }

    /**
     * Fetch a saved address, rejecting any id that does not belong to the given user.
     *
     * @param  int  $addressId
     * @param  int  $userId
     */
    protected function ownedAddress(int $addressId, int $userId)
    {
        $address = Address::where('id', $addressId)->where('user_id', $userId)->first();

        if (empty($address)) {
            throw ValidationException::withMessages([
                'address' => 'The selected address is not available on this account.',
            ]);
        }

        return $address;
    }

    /**
     * Persist a new one-off address for the user, flipping defaults only when explicitly asked.
     *
     * @param  array  $data
     * @param  int  $userId
     * @param  string  $side
     */
    protected function createOneOff(array $data, int $userId, string $side)
    {
        $saveAsDefault = ! empty($data[$side . '_save_as_default']);

        $address = Address::create([
            'user_id' => $userId,
            'label' => $data[$side . '_label'] ?? null,
            'recipient_name' => $data[$side . '_recipient_name'],
            'phone' => $data[$side . '_phone'],
            'address_line' => $data[$side . '_address_line'],
            'city' => $data[$side . '_city'] ?? null,
            'area' => $data[$side . '_area'] ?? null,
            'is_default_billing' => $this->defaultFlag($saveAsDefault, $side, 'billing'),
            'is_default_shipping' => $this->defaultFlag($saveAsDefault, $side, 'shipping'),
        ]);

        if (empty($saveAsDefault)) {
            return $address;
        }

        $this->clearOtherDefaults($address, $userId, $side);

        return $address;
    }

    /**
     * Whether a newly created address should carry the default flag for the given kind.
     *
     * @param  bool  $saveAsDefault
     * @param  string  $side
     * @param  string  $kind
     */
    protected function defaultFlag(bool $saveAsDefault, string $side, string $kind)
    {
        if (empty($saveAsDefault)) {
            return false;
        }

        return $side === $kind;
    }

    /**
     * Unset the previous default of the same kind so only one address holds it.
     *
     * @param  \App\Models\Address  $address
     * @param  int  $userId
     * @param  string  $side
     */
    protected function clearOtherDefaults(Address $address, int $userId, string $side)
    {
        Address::where('user_id', $userId)
            ->where('id', '!=', $address->id)
            ->update(['is_default_' . $side => false]);
    }

    /**
     * Mark a saved address as the user's default for the given kind.
     *
     * @param  \App\Models\Address  $address
     * @param  string  $kind
     */
    public function setDefault(Address $address, string $kind)
    {
        Address::where('user_id', $address->user_id)
            ->where('id', '!=', $address->id)
            ->update(['is_default_' . $kind => false]);

        $address->update(['is_default_' . $kind => true]);
    }
}
