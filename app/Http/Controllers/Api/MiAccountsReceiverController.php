<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MiAccountsReceiverController extends Controller
{
    /**
     * Report this storefront's catalogue state to MiAccounts.
     *
     * `data`  — products created here that MiAccounts has never taken.
     * `known` — id and status of every product MiAccounts already owns, so it
     *           can spot ones deactivated or deleted on this side. A product
     *           missing from `known` no longer exists here.
     *
     * Polled on a schedule. Read-only.
     */
    public function pendingProducts(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 50);

        $products = Product::whereNull('miaccounts_external_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->orderBy('products.id')
            ->limit($limit > 0 && $limit <= 200 ? $limit : 50)
            ->get([
                'products.id',
                'products.name',
                'products.price',
                'products.vat_rate',
                'products.stock_quantity',
                'products.short_description',
                'products.full_description',
                'categories.name as category',
            ]);

        $known = Product::whereNotNull('miaccounts_external_id')
            ->orderBy('id')
            ->get(['id', 'miaccounts_external_id', 'status']);

        return response()->json([
            'status' => true,
            'data' => $products,
            'known' => $known,
        ]);
    }

    /**
     * Receive a product/stock push from MiAccounts and upsert it by external id.
     */
    public function products(Request $request): JsonResponse
    {
        $data = $request->validate([
            'external_id' => ['required'],
            'external_product_id' => ['nullable'],
            'name' => ['required', 'string'],
            'category' => ['nullable', 'string'],
            'price' => ['required', 'numeric'],
            'vat_rate' => ['nullable', 'numeric'],
            'stock_quantity' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'full_description' => ['nullable', 'string'],
        ]);

        $product = $this->resolveProduct($data);

        $product->miaccounts_external_id = $data['external_id'];
        $product->category_id = $this->resolveCategoryId($data['category'] ?? null);
        $product->name = $data['name'];
        $product->slug = $this->uniqueSlug($data['name'], $product);
        $product->price = $data['price'];
        $product->vat_rate = $data['vat_rate'] ?? 0;
        $product->status = $data['status'] ?? 'active';
        $product->short_description = $data['short_description'] ?? null;
        $product->full_description = $data['full_description'] ?? null;
        $this->applyStock($product, $data);
        $product->save();

        return response()->json([
            'status' => true,
            'external_product_id' => $product->id,
        ]);
    }

    /**
     * Apply the pushed stock figure, but only when MiAccounts sent one.
     *
     * An integration without the `ecommerce_stock` grant omits the key
     * entirely, meaning this storefront owns its own stock. Writing a default
     * in that case would silently zero it on every unrelated product push.
     */
    private function applyStock(Product $product, array $data): void
    {
        if (! array_key_exists('stock_quantity', $data)) {
            return;
        }

        if ($data['stock_quantity'] === null) {
            return;
        }

        $product->stock_quantity = $data['stock_quantity'];
    }

    /**
     * Locate the product this push targets.
     *
     * Prefers our own id when MiAccounts sends it back — that is how a product
     * created here is claimed on first push instead of being duplicated.
     */
    private function resolveProduct(array $data): Product
    {
        if (! empty($data['external_product_id'])) {
            $local = Product::find($data['external_product_id']);

            if ($local !== null) {
                return $local;
            }
        }

        return Product::firstOrNew(['miaccounts_external_id' => $data['external_id']]);
    }

    /**
     * Find or create the category by name, falling back to an uncategorised bucket.
     */
    private function resolveCategoryId(?string $name): int
    {
        $label = blank($name) ? 'Uncategorised' : $name;

        return Category::firstOrCreate(
            ['slug' => Str::slug($label)],
            ['name' => $label, 'status' => 'active']
        )->id;
    }

    /**
     * Build a slug that stays unique against other products.
     */
    private function uniqueSlug(string $name, Product $product): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while ($this->slugTaken($slug, $product)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Check whether another product already holds this slug.
     */
    private function slugTaken(string $slug, Product $product): bool
    {
        return Product::where('slug', $slug)
            ->when($product->exists, function ($query) use ($product) {
                return $query->whereKeyNot($product->getKey());
            })
            ->exists();
    }

    /**
     * Receive an order status push from MiAccounts. Real update lands in P6.
     */
    public function orderStatus(Request $request): JsonResponse
    {
        Log::info('MiAccounts order status push received', ['payload' => $request->all()]);

        return response()->json(['status' => true]);
    }
}
