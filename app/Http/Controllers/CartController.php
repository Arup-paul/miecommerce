<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        $items = $this->cart->items();
        $grandTotal = $this->cart->grandTotal();

        return view('storefront.cart', compact('items', 'grandTotal'));
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($product->status !== 'active' || $product->stock_quantity < $data['quantity']) {
            return back()->with('error', 'This product is not available in the requested quantity.');
        }

        $this->cart->add($product->id, $data['quantity']);

        return back()->with('status', 'Added to cart.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($product->stock_quantity < $data['quantity']) {
            return back()->with('error', 'Not enough stock available.');
        }

        $this->cart->update($product->id, $data['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function remove(Product $product)
    {
        $this->cart->remove($product->id);

        return back()->with('status', 'Item removed from cart.');
    }
}
