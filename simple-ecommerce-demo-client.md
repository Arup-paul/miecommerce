# Simple E-commerce Project — Implementation Plan

## Context 

This is a standalone, independent e-commerce web application — not part of any other
codebase, with no external ERP webhook/sync logic in scope. The product and order data
model is kept shaped so that a sync mechanism could be attached later if ever needed, but
this plan builds none of that transport — no webhook controllers, no API-key middleware,
no outbound HTTP calls to anything. It is a real, minimal, self-contained storefront:
browse products, add to cart, check out, see an order confirmation, and manage the
catalog from a simple admin area.

## Stack

Laravel 10 (`php: ^8.1`, `laravel/framework: ^10.0`), MySQL for the DB (matching the
existing environment's stack), Blade views with Laravel Breeze-style simple auth for the
admin area (no SPA framework needed for this scope — plain Blade + a little vanilla JS
for the cart).

## Scope

A minimal but real storefront:
1. Product catalog: flat category system (Electronics, Clothing, etc. — one level, no
   sub-categories) + list/detail pages, each product has name, description, price,
   stock, image, status (active/inactive), and belongs to exactly one category.
2. Category browsing: a nav/sidebar list of categories linking to a per-category product
   listing page.
3. Cart: session-based (no login required to shop), add/remove/update quantity.
4. Checkout: a simple form (customer name, mobile, email, address) that converts the
   cart into an order.
5. Order confirmation page after checkout, showing what was ordered and the total.
6. Simple admin area (behind login) to create/edit/delete products and categories.

Not in scope: payment gateway integration, customer accounts/login for shoppers, product
search, nested/hierarchical categories, and — per this plan's instruction — no external
sync/webhook logic of any kind. The data model is deliberately kept simple enough that
sync could be layered on top later without a redesign, but nothing here calls out to or
receives calls from any other system.

---

## 1. Project setup

New standalone project directory, e.g. `/var/www/html/simple-ecommerce/`:

```
composer create-project laravel/laravel simple-ecommerce "^10.0"
```

`.env` DB config:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simple_ecommerce
DB_USERNAME=root
DB_PASSWORD=
```
Create the database first (`mysql -u root -e "CREATE DATABASE simple_ecommerce"` or via
whatever local MySQL client is available), then `php artisan migrate`.

Auth for the admin area: `composer require laravel/breeze --dev` then `php artisan
breeze:install blade` — gives a working login/register scaffold quickly, reused as-is
for gating `/admin/*` routes rather than hand-rolling auth.

---

## 2. Data model

### Migration: `categories`

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('status', 20)->default('active');
    $table->timestamps();
});
```

Model `app/Models/Category.php` — fillable: `name, slug, status`; `hasMany(Product::class)`.
`slug` auto-generated from `name` on save, same pattern as `Product` below. Flat list —
no `parent_id`/self-reference, by design (kept to one level for this project's scope).

### Migration: `products`

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('category_id');
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('short_description')->nullable();
    $table->text('full_description')->nullable();
    $table->decimal('price', 15, 2);
    $table->decimal('vat_rate', 5, 2)->default(0);
    $table->integer('stock_quantity')->default(0);
    $table->string('status', 20)->default('active');
    $table->string('image_path')->nullable();
    $table->timestamps();

    $table->foreign('category_id')->references('id')->on('categories');
});
```

`vat_rate` (percent, e.g. `15.00`) makes the order/order_items VAT columns introduced
below actually meaningful — without it there was no source for VAT and those columns
would always compute to zero, which defeats the point of mirroring the ERP's
subtotal/VAT/total breakdown.

Model `app/Models/Product.php` — fillable: `category_id, name, slug, short_description,
full_description, price, vat_rate, stock_quantity, status, image_path`;
`belongsTo(Category::class)`.
`slug` auto-generated from `name` on save (via a model `saving` event using
`Str::slug()`) — used for the product detail page URL instead of exposing raw IDs.
`category_id` is required (not nullable) — every product must belong to a category, so
category admin must seed/create at least one category before the first product can be
created.

### Migration: `orders`

Field names and status vocabulary deliberately mirror the ERP's own
`sales_order_masters` / e-commerce order workflow shape (`bill_number`/`order_number`,
`subtotal_amount`/`vat_amount`/`total_amount`, `payment_status`, `order_status` with the
same stage names) — not because this project syncs with that ERP, but so the two data
models read as natural counterparts and a future sync layer (if ever built) would map
field-to-field rather than needing translation. This project has no `account_head_id`,
`business_id`, `location_id`, etc. — those are ERP-internal accounting concepts with no
equivalent here — only the customer/order/payment/status shape is mirrored.

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique();
    $table->string('customer_name');
    $table->string('customer_mobile', 30);
    $table->string('customer_email')->nullable();
    $table->string('customer_address');
    $table->string('shipping_city', 120)->nullable();
    $table->string('shipping_area', 120)->nullable();
    $table->decimal('subtotal_amount', 15, 2);
    $table->decimal('vat_amount', 15, 2)->default(0);
    $table->decimal('discount_amount', 15, 2)->default(0);
    $table->decimal('shipping_amount', 15, 2)->default(0);
    $table->decimal('total_amount', 15, 2);
    $table->string('payment_method', 30)->default('cash_on_delivery');
    $table->string('payment_status', 20)->default('due');
    $table->string('order_status', 20)->default('pending');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

- `payment_method`: `cash_on_delivery | card | mobile_banking | bank_transfer`.
- `payment_status`: `due | paid | partially_paid | refunded` — separate from
  fulfillment, matching the ERP's split between `payment_status` and `order_status`.
- `order_status` (fulfillment workflow, same stage vocabulary as the ERP's
  `order_workflow_status`): `pending → confirmed → ready_to_ship → shipped → delivered`,
  with `cancelled` and `returned` as terminal side-branches. Kept as a plain string
  column (not an enum table) — same approach the ERP itself uses.

`order_number` generated at creation (e.g. `'ORD-' . now()->format('Ymd') . '-' .
str_pad($nextSequence, 4, '0', STR_PAD_LEFT)`) — human-readable reference shown on the
confirmation page, playing the same role as the ERP's `bill_number`.

Model `app/Models/Order.php` — fillable: all columns above except `id`/timestamps;
`hasMany(OrderItem::class)`; small static array constants (mirroring the ERP's
`InventoryHandler` trait pattern) used by both validation and the admin status-update
dropdown, so the allowed values/transitions live in one place:
```php
public static $orderStatuses = ['pending', 'confirmed', 'ready_to_ship', 'shipped', 'delivered', 'cancelled', 'returned'];
public static $paymentStatuses = ['due', 'paid', 'partially_paid', 'refunded'];
public static $orderStatusTransitions = [
    'pending' => ['confirmed', 'cancelled'],
    'confirmed' => ['ready_to_ship', 'cancelled'],
    'ready_to_ship' => ['shipped', 'cancelled'],
    'shipped' => ['delivered'],
    'delivered' => ['returned'],
    'cancelled' => [],
    'returned' => [],
];
```

### Migration: `order_items`

Mirrors `sales_order_generals`' commercial fields (`rate`, `quantity`, `vat`,
`vat_amount`, `discount_amount`, `total_amount`) at the line level, again without any
ERP-only dimension columns (`project_id`, `cost_centre_id`, etc.).

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('order_id');
    $table->unsignedBigInteger('product_id');
    $table->string('product_name');
    $table->decimal('rate', 15, 2);
    $table->integer('quantity');
    $table->decimal('vat_percent', 5, 2)->default(0);
    $table->decimal('vat_amount', 15, 2)->default(0);
    $table->decimal('discount_amount', 15, 2)->default(0);
    $table->decimal('total_amount', 15, 2);
    $table->timestamps();

    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
    $table->foreign('product_id')->references('id')->on('products');
});
```

`product_name`/`rate` are captured at order time (not joined live from `products`) so
historical orders stay accurate even if a product's name or price changes later — a
standard order-line snapshot pattern, same rationale the ERP applies by storing
`sales_order_generals.rate` rather than re-deriving it from the live product. Formula
matches the ERP's: `line_subtotal = rate * quantity`; `total_amount = (line_subtotal -
discount_amount) + vat_amount`.

Model `app/Models/OrderItem.php` — fillable: all columns above; `belongsTo(Order::class)`,
`belongsTo(Product::class)`.

---

## 3. Storefront (public, no login)

### Routes — `routes/web.php`

```php
Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/categories/{category:slug}', [StorefrontController::class, 'category'])->name('categories.show');
Route::get('/products/{product:slug}', [StorefrontController::class, 'show'])->name('products.show');

Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/orders/{order:order_number}/confirmation', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');
```

### `app/Http/Controllers/StorefrontController.php`

- `index()` — `Product::where('status', 'active')->paginate(12)` → `resources/views/storefront/index.blade.php` (grid of product cards), plus `Category::where('status', 'active')->get()` passed to the shared layout for the nav/sidebar category list.
- `category(Category $category)` — guard clause: 404 if `$category->status !== 'active'`; else `$category->products()->where('status', 'active')->paginate(12)` → `resources/views/storefront/category.blade.php` (same product-grid partial as `index`, scoped to one category, with the category name as page heading).
- `show(Product $product)` — single product detail + "Add to Cart" form (quantity input); includes a breadcrumb/link back to `$product->category`.

### Cart: session-based, `app/Services/CartService.php`

A small service wrapping `session()->get('cart', [])`, keyed by `product_id => quantity`.
Methods: `add(int $productId, int $qty)`, `update(int $productId, int $qty)`,
`remove(int $productId)`, `items(): Collection` (hydrates product rows + computes
line/grand totals), `clear()`. Kept as a service (not inline in the controller) so both
`CartController` and `CheckoutController` can reuse `items()`/`clear()` without
duplicating session logic — this is the one small abstraction, justified because it's
used from two different controllers per the project's "no premature abstraction" rule.

### `app/Http/Controllers/CartController.php`

Thin — guard-clause validate quantity > 0 and product is active/in-stock, delegate to
`CartService`, redirect back with a flash message. `index()` renders
`resources/views/storefront/cart.blade.php`.

### `app/Http/Controllers/CheckoutController.php`

- `index()` — guard clause: redirect to cart if empty; else render
  `resources/views/storefront/checkout.blade.php` with cart contents + customer form
  (name, mobile, email, address, city, area, payment method select).
- `store(Request $request)` — validate `customer_name`, `customer_mobile`,
  `customer_address`, `payment_method` required, `customer_email` nullable-but-must-be-a
  valid email format if present (guard clause first); guard clause: re-check each cart
  line's `product->stock_quantity >= quantity` and that the product is still `active` —
  stock/status can change between "add to cart" and checkout, so this is re-validated
  here, not only in `CartController` — redirect back to cart with an error listing which
  item(s) are no longer available if the check fails; then wrap in `DB::transaction()`:
  1. Compute per-line `vat_amount = round((rate * quantity - discount_amount) *
     product.vat_rate / 100, 2)` and `total_amount = (rate * quantity -
     discount_amount) + vat_amount`, using each product's own `vat_rate` (§2) — sum lines
     into the order's `subtotal_amount`/`vat_amount`.
  2. `shipping_amount` is a fixed flat value for this project (e.g. a `SHIPPING_FLAT_RATE`
     config value, or 0 for simplicity) — no shipping-cost calculator is in scope; this
     keeps the field from being dead/unset while not over-building a rate engine.
  3. Create `Order` (`payment_status='due'`, `order_status='pending'`,
     `total_amount = subtotal_amount + vat_amount + shipping_amount - discount_amount`).
  4. Create one `OrderItem` per cart line (snapshotting `product_name`/`rate`/`vat_percent`
     at that moment).
  5. Decrement each `Product.stock_quantity` by the ordered quantity.
  6. `CartService::clear()`.
  7. Redirect to `orders.confirmation`.
- `confirmation(Order $order)` — order-summary view showing the full breakdown
  (subtotal, VAT, discount, shipping, total) and current `order_status`/`payment_status`,
  no auth needed (found via the unguessable-ish `order_number` route key).

---

## 4. Admin area (behind login)

### Routes — `routes/web.php`, under `auth` middleware (from Breeze) + a `prefix('admin')` group

```php
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', Admin\CategoryController::class)->except(['show']);
    Route::resource('products', Admin\ProductController::class);
    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('orders/{order}/payment-status', [Admin\OrderController::class, 'updatePaymentStatus'])->name('orders.payment-status');
});
```

### `app/Http/Controllers/Admin/CategoryController.php`

Standard Laravel resource controller (`index`, `create`, `store`, `edit`, `update`,
`destroy`) — thin, delegating straight to `Category::create()`/`update()`. `destroy()`
guard clause: block deletion (redirect back with an error) if the category still has
products (`$category->products()->exists()`) rather than cascading — an admin must
reassign or delete those products first, avoiding orphaned/silently-recategorized
products.

### `app/Http/Controllers/Admin/ProductController.php`

Standard Laravel resource controller (`index`, `create`, `store`, `edit`, `update`,
`destroy`) — thin, delegating persistence straight to `Product::create()`/`update()`
(no separate service layer needed here; this is plain CRUD with no cross-cutting
business logic, unlike the product-sync case in the other plan). `create()`/`edit()`
pass `Category::where('status', 'active')->get()` for the category `<select>`. Image
upload via `$request->file('image')->store('products', 'public')` saved into
`image_path`.

### `app/Http/Controllers/Admin/OrderController.php`

- `index()` — paginated order list, filterable by `order_status` via a query-string tab
  (mirroring the ERP e-commerce board's tab-per-status pattern, kept simple here as a
  `?status=` filter rather than a full kanban).
- `show(Order $order)` — order detail with line items, full amount breakdown, and
  current `payment_status`/`order_status`.
- `updateStatus(Request $request, Order $order)` — guard clause: reject if the
  requested `order_status` is not in `Order::$orderStatuses`, and reject if it is not
  one of the currently-allowed next stages for `$order->order_status` per
  `Order::$orderStatusTransitions` (same guard the ERP applies via its
  `$orderWorkflowTransitions` map — prevents e.g. jumping `pending` straight to
  `delivered`, or moving a `cancelled` order back to `pending`); update, redirect back.
- `updatePaymentStatus(Request $request, Order $order)` — validate `payment_status` is
  one of `Order::$paymentStatuses` (`due, paid, partially_paid, refunded`) via guard
  clause, update, redirect back. Kept as a separate action from `updateStatus` since
  payment and fulfillment are independent tracks, same separation the ERP itself makes.

---

## 5. Views (Blade, plain CSS — no build step)

- `resources/views/storefront/index.blade.php` — product grid.
- `resources/views/storefront/category.blade.php` — same grid, scoped to one category.
- `resources/views/storefront/show.blade.php` — product detail + add-to-cart form.
- `resources/views/storefront/cart.blade.php` — cart table with quantity update/remove.
- `resources/views/storefront/checkout.blade.php` — customer form + order summary.
- `resources/views/storefront/confirmation.blade.php` — "Thank you" + order number + items.
- `resources/views/storefront/partials/product-grid.blade.php` — shared partial for the
  product-card grid, included by both `index.blade.php` and `category.blade.php` to
  avoid duplicating markup.
- `resources/views/admin/categories/index.blade.php`, `create.blade.php`, `edit.blade.php`.
- `resources/views/admin/products/index.blade.php`, `create.blade.php`, `edit.blade.php`.
- `resources/views/admin/orders/index.blade.php`, `show.blade.php`.
- Shared `resources/views/layouts/storefront.blade.php` (renders the category
  nav/sidebar on every storefront page) and reuse Breeze's `layouts/app.blade.php` for
  admin pages.

---

## 6. Sequencing

1. `composer create-project laravel/laravel` scaffold + MySQL database creation + `.env`
   configuration (§1).
2. Install Breeze, run its migrations (gives `users` table + login/register views) for
   the admin area.
3. `categories`, `products`, `orders`, `order_items` migrations + models (§2) — in that
   order, since `products.category_id` has a foreign key to `categories`.
4. `CartService` (§3) — testable standalone via `php artisan tinker` before wiring
   controllers.
5. Storefront controllers + routes + views: category nav, category listing, product
   listing/detail, cart, checkout, confirmation (§3, §5).
6. Admin controllers + routes + views: category CRUD, product CRUD, order
   list/detail/status (§4, §5).
7. Seed a few categories + a handful of demo products per category
   (`database/seeders/CategorySeeder.php`, `ProductSeeder.php`) so the storefront isn't
   empty on first run.

### Critical files (new project, all newly created)

- `routes/web.php` — storefront + admin routes
- `app/Models/Category.php`, `Product.php`, `Order.php`, `OrderItem.php`
- `app/Services/CartService.php` — session cart logic, shared by two controllers
- `app/Http/Controllers/StorefrontController.php`, `CartController.php`, `CheckoutController.php`
- `app/Http/Controllers/Admin/CategoryController.php`, `Admin/ProductController.php`, `Admin/OrderController.php`
- `database/migrations/*_create_categories_table.php`, `*_create_products_table.php`, `*_create_orders_table.php`, `*_create_order_items_table.php`
- `database/seeders/CategorySeeder.php`, `ProductSeeder.php`
- `resources/views/storefront/*.blade.php`, `resources/views/admin/**/*.blade.php`

---

## Verification

1. Create the MySQL database, run `php artisan migrate --seed` then `php artisan serve`;
   visit `/` and confirm seeded categories appear in the nav/sidebar and seeded products
   render in the grid.
2. Click a category in the nav; confirm `/categories/{slug}` shows only that category's
   active products.
3. Click into a product detail page, add to cart with a quantity, confirm the cart page
   reflects it with correct line/grand totals.
4. Update quantity and remove an item from the cart; confirm totals recalculate.
5. Complete checkout with a test customer, selecting a payment method; confirm redirect
   to the confirmation page showing the correct order number, items, subtotal/VAT/
   discount/shipping/total breakdown, and default `order_status='pending'` /
   `payment_status='due'`; confirm the product's `stock_quantity` decremented in the DB.
6. Register/login via Breeze, visit `/admin/categories`; create a new category, confirm
   it appears in the storefront nav and as a selectable option on the product create form.
7. Visit `/admin/products`; create, edit, and delete a product; confirm changes reflect
   on the public storefront and category page immediately.
8. Attempt to delete a category that still has products; confirm it's blocked with an
   error message rather than deleting/orphaning the products.
9. Visit `/admin/orders`, filter by `order_status`, open the order placed in step 5,
   advance its `order_status` through allowed stages (e.g. `pending → confirmed →
   ready_to_ship → shipped → delivered`) and separately update `payment_status` (e.g.
   `due → paid`); confirm both persist independently of each other.
10. Attempt an invalid jump (e.g. `pending → delivered` directly, or updating a
    `cancelled` order back to `pending`); confirm `Order::$orderStatusTransitions`
    blocks it with an error rather than silently applying it.
11. Add a product to the cart, then in another tab/admin session reduce that product's
    `stock_quantity` below the cart quantity (or set it inactive); attempt checkout;
    confirm it's rejected with a clear error rather than creating an order against
    insufficient stock.
12. Confirm order-line VAT is computed correctly: set a product's `vat_rate` to a
    non-zero value, order it, and confirm `order_items.vat_amount` and the order's
    `vat_amount`/`total_amount` reflect that rate.
