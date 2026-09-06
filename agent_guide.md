# Agent Guide — Backend-POS

A guide for AI agents / developers to quickly understand this project. Read this before modifying code.

## 1. What this is

A **Laravel 10 REST API backend** for an **Inventory / Point-of-Sale (POS)** system. It exposes a pure JSON API (the only web route is the default Laravel welcome page). The "Object Model" is complete CRUD scaffolding for ~20 business entities (branches, warehouses, products, customers, sales, purchases, returns, roles, users, etc.).

- **Framework:** Laravel 10 (`laravel/framework: ^10.0`, locked v10.50.3)
- **PHP:** `^8.1`
- **Auth package:** `laravel/sanctum: ^3.2` — **installed but NOT wired up yet** (see §8)
- **DB:** MySQL (`Inventory-POS-System`, port `3307` in `.env`)
- **Frontend build:** Vite (bare scaffold, Axios only; no Vue/React/Tailwind). The real frontend lives in the sibling `Frontend-POS` directory.
- **Tests:** scaffold only (`tests/Unit/ExampleTest.php`, `tests/Feature/ExampleTest.php`). No API tests.

## 2. Directory map (only project-owned code)

```
Backend-POS/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/     # 20 resource controllers (thin CRUD)
│   │   ├── Requests/            # 38 FormRequest classes (Store/Update pairs)
│   │   ├── Resources/           # 19 JsonResource classes
│   │   └── Middleware/          # ForceJsonResponse (custom)
│   ├── Models/                  # Eloquent models
│   ├── Providers/               # RouteServiceProvider, AppServiceProvider, etc.
│   └── Exceptions/Handler.php   # JSON error formatting
├── config/                      # Laravel config (cors, sanctum, database, ...)
├── database/
│   ├── migrations/              # 23 files
│   └── seeders/                 # DatabaseSeeder, RoleSeeder, RolePermissionSeeder
├── routes/
│   ├── api.php                  # ALL real endpoints (see §4)
│   └── web.php                  # default welcome page only
├── tests/                       # scaffold only
└── .env                         # DB name Inventory-POS-System, port 3307 (do NOT commit)
```

> `vendor/`, `node_modules/`, `storage/` are dependencies/cache — never touch. There are **no** services, repositories, jobs, listeners, policies, or observers layers; all business logic lives in controllers/models.

## 3. Architecture & conventions (agreed patterns)

The codebase is highly repetitive. **The CRUD pattern below is safe to assume for any resource** unless noted otherwise.

### Controller pattern (`app/Http/Controllers/Api/*Controller.php`)
- `index(Request $request): AnonymousResourceCollection` — optional query filters via `if ($request->filled('x'))`, then `->paginate($request->integer('per_page', 15))`, `latest()`, wrap in `XResource::collection(...)`.
- `store(StoreXRequest $request): JsonResponse` — `Model::create($request->validated())`, then return `(new XResource($model))->response()->setStatusCode(201)`. **Exceptions:** `SaleController@store` returns custom `{message, data}` / `{message, error}`; `UserController` maps `password` → `password_hash`.
- `show` / `update` — return `XResource`; `update(UpdateXRequest $request, Model $model)`.
- `destroy` — `$model->delete()` → `200 {"message": "... deleted successfully."}`. `RoleController@destroy` returns `403` for `grant_all` (superadmin) roles.

### Form Requests (`app/Http/Requests/*`)
- `authorize()` always returns `true` — **no authorization checks anywhere**.
- Rules use array form (e.g. `['nullable', 'integer', 'exists:branches,id']`), `unique:` on codes/SKUs/barcodes/emails, and `Rule::unique(...)->ignore($routeParamId)` in Update requests.
- The route-param-to-id resolution is copy-pasted in every Update request:
  ```php
  $productId = $this->route('product') instanceof \App\Models\Product
      ? $this->route('product')->id : $this->route('product');
  ```
- Common values: `status in:active,archived,draft`, `payment_method in:cash,card,qr,bank_transfer,mobile_payment,credit,mixed`, money `numeric min:0`, `tax between:0,100`.

### Resources (`app/Http/Resources/*.php`)
- Timestamps → `$this->created_at?->toIso8601String()`.
- Money/stock → cast `(float)`.
- Relations rendered conditionally: `new XResource($this->whenLoaded('relation'))`.

### Pagination & response shape
- List endpoints return Laravel's standard paginator envelope: `{ data: [...], links: {...}, meta: {...} }`.
- `per_page` default `15`; **line-item lists (`SaleItem`, `ReturnItem`) default to `50`**.
- Validation failures → `Handler::invalidJson()` returns `{ message, errors }` (custom override).
- `store` → `201`; `destroy` → `200 {message}`; errors → JSON via `Handler::prepareJsonResponse`.

## 4. Routing

All real routes live in `routes/api.php`, applied with the `api` middleware group + `api/` prefix. **No API versioning** (no `/v1`).

- **19 `Route::apiResource(...)` resources** → 5 routes each (`index/show/store/update/destroy`):

  | Prefix | Controller |
  |---|---|
  | `api/branches` | `Api\BranchController` |
  | `api/warehouses` | `Api\WarehouseController` |
  | `api/registers` | `Api\RegisterController` |
  | `api/roles` | `Api\RoleController` |
  | `api/users` | `Api\UserController` |
  | `api/customer-groups` | `Api\CustomerGroupController` |
  | `api/customers` | `Api\CustomerController` |
  | `api/categories` | `Api\CategoryController` |
  | `api/brands` | `Api\BrandController` |
  | `api/units` | `Api\UnitController` |
  | `api/products` | `Api\ProductController` |
  | `api/product-variants` | `Api\ProductVariantController` |
  | `api/suppliers` | `Api\SupplierController` |
  | `api/sales` | `Api\SaleController` |
  | `api/sale-items` | `Api\SaleItemController` |
  | `api/held-sales` | `Api\HeldSaleController` |
  | `api/returns` | `Api\ReturnController` |
  | `api/return-items` | `Api\ReturnItemController` |
  | `api/purchases` | `Api\PurchaseController` |

- **2 custom routes** (role permissions):
  - `GET  api/roles/{role}/permissions` → `RolePermissionController@index`
  - `POST api/roles/{role}/permissions` → `RolePermissionController@sync` (transactional replace)
- Route model binding used everywhere.
- **No `auth:sanctum` middleware is applied to any route yet.**

## 5. Domain model at a glance

| Model | Table | Key fields / relations | Notes |
|---|---|---|---|
| `Branch` | `branches` | hasMany registers, warehouses, users | |
| `Warehouse` | `warehouses` | belongsTo branch | |
| `Register` | `registers` | belongsTo branch (POS cash drawer) | |
| `Role` | `roles` | `key, grant_all`, hasMany permissions | helper `hasPermission()`. ⚠️ `users()` declared `BelongsTo` (bug) |
| `RolePermission` | `role_permissions` | composite PK `(role_id, permission)`, no timestamps | |
| `User` | `users` | role_id, branch_id, **`password_hash`** (not `password`!) | custom column ⚠️ see §8 |
| `CustomerGroup` | `customer_groups` | name, discount_percent | |
| `Customer` | `customers` | group_id, loyalty_points, total_spent, outstanding | |
| `Category` / `Brand` / `Unit` | … | belongsTo **HasMany** products | |
| `Product` | `products` | category/brand/unit_id, sku, barcode, cost, price, wholesale_price, tax_percent, `track_inventory`, min/max_stock, stock | |
| `ProductVariant` | `product_variants` | product_id, sku, barcode, cost, price, stock | |
| `Supplier` | `suppliers` | contact_person, tax_number, total_purchases, outstanding | |
| `Sale` | `sales` | invoice_number, customer_id, cashier_id, branch_id, register_id, subtotal/discount/tax/total/paid/change, payment_method, status, payment_status | `creating` hook auto-generates `INV-YYYYMMDD-0001`; NOT applied in any controller though — see "Known gaps" §9 |
| `SaleItem` | `sale_items` | sale_id, product/variant_id, name, sku, price, cost, quantity, discount, tax; no `updated_at` | accessor `getLineTotalAttribute()` |
| `HeldSale` | `held_sales` | hold_number, customer/cashier_id, discounts, `items_json` (cast array); no `updated_at` | hook generates `HOLD-###`, controller does NOT persist items anywhere else |
| `SaleReturn` | `sale_returns` | sale_id, branch_id, cashier_id, reason, refund_amount | ⚠️ route prefix is `returns`, controller `ReturnController` |
| `ReturnItem` | `return_items` | return_id, product/variant_id, name, sku, price, cost, quantity, discount, tax; no `updated_at` | |
| `Purchase` | `purchases` | purchase_number, supplier/branch/warehouse/created_by, order_date/expected_date/received_at, totals, status, payment_status | |

DB quirks to respect:
- Money → `decimal(14,2)`; stock → `decimal(14,3)`. `sale_items`/`return_items`/`held_sales` have **no `updated_at`**.
- FK conventions: cascades for "owned" children (`warehouses.branch_id`, `sale_items.sale_id`), `nullOnDelete` for optional references.

## 6. Index filters & eager loading cheat-sheet

| Controller `index` | Eager loads | Notable filters |
|---|---|---|
| `Branch` | — | status; search name/code |
| `Warehouse` | branch | branch_id, status |
| `Register` | branch | branch_id, status |
| `Role` | — | search; grant_all |
| `User` | role, branch | role_id, branch_id, status; search name/email/phone |
| `Customer` | group | group_id, status, `has_outstanding` |
| `Product` | category, brand, unit | category_id, brand_id, status, `low_stock` (= `track_inventory && stock <= min_stock`) |
| `ProductVariant` | product | product_id |
| `Supplier` | — | status, `has_outstanding` |
| `Sale` | customer, cashier, branch, register | branch_id, cashier_id, customer_id, status, payment_status, payment_method, `from_date`/`to_date`, search invoice_number |
| `SaleItem` / `ReturnItem` | product, variant | parent id; per_page default 50 |
| `HeldSale` | customer, cashier | cashier_id, search hold_number |
| `Return` | sale, branch, cashier | sale_id, branch_id, cashier_id, search reason |

## 7. Seed data

Run with `php artisan db:seed` (or `php artisan migrate --seed`):
- `RoleSeeder`: `admin` (grant_all=true), `manager`, `cashier`, `accountant`, `viewer`.
- `RolePermissionSeeder`: dotted permission strings (`sales.view`, `sales.create`, `products.view`, `inventory.*`, `reports.view`, ...).
- `DatabaseSeeder`: roles, branch #1, register, cashier user `budi.cashier@pos.com`, VIP customer group, customer, warehouse, category, brand, unit, supplier, one product (Coca-Cola 330ml).

## 8. Authentication status ⚠️ IMPORTANT

- **Sanctum is installed but NOT implemented.** There are:
  - no login/register/logout routes, no `AuthController`, no `createToken()` calls;
  - no `auth:sanctum` middleware on any route;
  - `EnsureFrontendRequestsAreStateful` middleware commented out in `app/Http/Kernel.php`.
- Custom RBAC exists in data (roles + permissions + `Role::hasPermission()`) but is **not enforced** anywhere.
- **Gotcha:** the `users` table uses `password_hash` (not Laravel's standard `password`) and `User` has **no** `getAuthPassword()` override — standard auth will not work until this is handled.
- CORS (`config/cors.php`): `api/*` + `sanctum/csrf-cookie`, origins `*`, credentials disabled.
- Sanctum config: stateful domains include `localhost:3000` (the frontend dev port); token expiry `null` (never).
- Full-stack flow: the sibling `Frontend-POS` app is the intended API client.

## 9. Known gaps / inconsistencies (fix with care)

1. **Inventory is never decremented** on sales/returns — `SaleController@store` and `ReturnController@store` do not adjust `product.stock`. Purchases likewise don't touch stock.
2. **`Sale` / `HeldSale` `creating` hooks** auto-generate `invoice_number`/`hold_number`, but **only `HeldSale` actually persists through a controller that triggers it** — `SaleController@store` uses `Sale::create()` so the hook runs; verify numbers are being generated as expected.
3. **`User::password_hash` vs `password`** mismatch (§8).
4. **`Role::users()`** is declared as `BelongsTo` but returns many users → relationship bug.
5. **`ReturnController` ↔ `SaleReturn` model ↔ `SaleReturnResource`** naming mismatch with the `returns` route prefix. `ReturnItem` has `return()` → `SaleReturn`.
6. **`BranchResource`** contains a leftover `warehouses()` relationship method (dead code).
7. **`UserFactory`** still uses the standard `password` field, inconsistent with the DB's `password_hash` column.
8. **No tests** for API behavior; the `web` limiter rate-limit is `ThrottleRequests:api` 60/min per user/IP.

## 10. Common commands

```bash
composer install
npm install && npm run build        # vite build of the bare scaffold
cp .env.example .env                 # then set DB_DATABASE=Inventory-POS-System, DB_PORT=3307
php artisan key:generate
php artisan migrate --seed           # MySQL: db Inventory-POS-System, port 3307, user root, empty password
php artisan serve                    # serves on :8000 by default
php artisan route:list               # view the 97 api routes
```

## 11. Golden rules for editing this codebase

- Follow the **existing template pattern** (controller → FormRequest → Resource → model) exactly; mirror a sibling file for any new resource.
- Keep filters/validation consistent (filled() guards, `per_page`, enum `in:` rules, `exists:` FKs).
- **Never commit `.env`.** Match the sibling `.env.example` if you change config.
- When adding feature logic (e.g. stock adjustments, auth), prefer the pattern of `RolePermissionController@sync` (transaction, inline validation) and `SaleController@store` (custom response shape) as the closest existing references.
- If behavior must diverge from the common CRUD template, document it here.