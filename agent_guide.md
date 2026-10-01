# Agent Guide — Backend-POS

A guide for AI agents / developers to quickly understand this project. Read this before modifying code.

## 1. What this is

A **Laravel 10 REST API backend** for an **Inventory / Point-of-Sale (POS)** system, **wired to the sibling `Frontend-POS` React app**. It exposes a pure JSON API protected by **Sanctum bearer tokens**. The "Object Model" is full CRUD scaffolding for ~30 business entities plus auth, dashboard, reports, and settings.

- **Framework:** Laravel 10 (`laravel/framework: ^10.0`, locked v10.50.3)
- **PHP:** `^8.1`
- **Auth:** `laravel/sanctum: ^3.2` — **fully wired** (login/me/logout + `auth:sanctum` middleware on all protected routes; see §8)
- **DB:** MySQL (`Inventory-POS-System`, port `3307` in `.env`, user `root`, no password)
- **Frontend client:** the sibling `Frontend-POS` app (`VITE_API_URL=http://localhost:8000/api`). Backend-POS itself contains only the default Vite scaffold (no Vue/React/Tailwind in use).
- **Tests:** scaffold only (`tests/Unit/ExampleTest.php`, `tests/Feature/ExampleTest.php`). No API tests.
- **Total surface:** 33 controllers, 53 FormRequests, 28 Resources, 30 models, 33 migrations. 151 `api/*` routes.

## 2. Directory map (only project-owned code)

```
Backend-POS/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/     # 33 resource + feature controllers (thin CRUD + auth/dashboard/reports/settings)
│   │   ├── Requests/            # 53 FormRequest classes (Store/Update pairs)
│   │   ├── Resources/           # 28 JsonResource classes
│   │   └── Middleware/          # ForceJsonResponse (custom)
│   ├── Models/                  # 30 Eloquent models
│   ├── Providers/               # RouteServiceProvider, AppServiceProvider, etc.
│   └── Exceptions/Handler.php   # JSON error formatting
├── config/                      # Laravel config (cors, sanctum, database, ...)
├── database/
│   ├── migrations/              # 33 files
│   └── seeders/                 # DatabaseSeeder + per-domain seeders (roles, permissions, admin, audit logs, ...)
├── routes/
│   ├── api.php                  # ALL real endpoints (see §4)
│   └── web.php                  # default welcome page only
├── tests/                       # scaffold only
└── .env                         # DB name Inventory-POS-System, port 3307 (do NOT commit)
```

> `vendor/`, `node_modules/`, `storage/` are dependencies/cache — never touch. There are **no** services/repositories/policies layers; business logic lives in controllers/models (with a few DB-transaction workflows in the "smart" controllers listed in §3).

## 3. Architecture & conventions (agreed patterns)

The codebase is highly repetitive. **The CRUD pattern below is safe to assume for any resource** unless noted otherwise.

### Controller pattern (`app/Http/Controllers/Api/*Controller.php`)
- `index(Request $request): AnonymousResourceCollection` — optional query filters via `if ($request->filled('x'))`, then `->paginate($request->integer('per_page', 15))`, `latest()`, wrap in `XResource::collection(...)`.
- `store(StoreXRequest $request): JsonResponse` — `Model::create($request->validated())`, then return `(new XResource($model))->response()->setStatusCode(201)`.
- `show` / `update` — return `XResource`; `update(UpdateXRequest $request, Model $model)`.
- `destroy` — `$model->delete()` → `200 {"message": "... deleted successfully."}`. `RoleController@destroy` returns `403` for `grant_all` (superadmin) roles.

**Not thin CRUD — the "smart" controllers (don't assume plain CRUD here):**
- `SaleController@store` — transactional: creates the sale, persists `items`, **decrements stock** (asserts sufficiency first), logs `StockMovement`. Returns `{message, data}` / `{message, error}` (custom error shape).
- `PurchaseController@store/update/destroy` — transactional; `receive()` restocks warehouse products + logs movements; destroy on a received order reverses stock.
- `ReturnController@store/destroy` — transactional; increments stock back + logs movements; destroy reverses.
- `StockMovementController@store` — applies the movement to product/variant stock in a transaction and auto-computes `before_stock`/`after_stock`.
- `CashTransactionController@store/destroy` — updates the parent session's `expected_cash` in a transaction.
- `CashRegisterSessionController@store/update` — sets `opened_at`/`expected_cash` (model `creating` hook) and computes `difference` + `closed_at` on close.
- `RolePermissionController@sync` — transactional replace of a role's permission set.
- `AuthController` + `DashboardController` + `ReportController` + `SettingController` — feature endpoints, not CRUD.

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

All real routes live in `routes/api.php` under the `api` + `auth:sanctum` middleware group. **No API versioning** (no `/v1`). `POST api/auth/login`, `POST api/auth/forgot-password`, and `POST api/auth/reset-password` are public; everything else requires a Sanctum bearer token. A `fallback` route returns 404 JSON for unknown `/api/*` paths.

Resource groups (all `Route::apiResource(..., ...)` → index/show/store/update/destroy unless noted):

| Group | Resources / controllers |
|---|---|
| Auth | `POST auth/login`, `POST auth/forgot-password`, `POST auth/reset-password` (public); `GET auth/me`, `POST auth/logout`, `POST auth/change-password` |
| Access & users | `branches`, `warehouses`, `registers`, `roles` (with `roles/{role}/permissions` GET+POST/PUT), `users` |
| Customers & suppliers | `customer-groups`, `customers`, `suppliers` |
| Catalog & inventory | `categories`, `brands`, `units`, `products`, `product-variants` |
| Sales & returns | `sales`, `sale-items`, `held-sales`, `returns`, `return-items`, `transfer-items` |
| Procurement & finance | `purchases`, `purchase-items`, `stock-movements` (**only index/store/show**), `stock-transfers`, `expenses`, `cash-register-sessions`, `cash-transactions` |
| Notifications | `notifications` (**except update**) + `POST notifications/read-all`, `PATCH notifications/{id}/read` |
| Audit | `audit-logs` (only index/show/store) |
| Settings | `GET settings`, `GET settings/{key}`, `POST|PUT settings` |
| Dashboard & reports | `GET dashboard`, `GET reports/sales|purchases|inventory|profit` |
| Fallback | `/{fallbackPlaceholder}` → 404 JSON |

- Route model binding used everywhere.
- **`auth:sanctum` is applied to the whole group** — every endpoint except `auth/login` requires a bearer token (`Authenticate:sanctum` shown on all api routes in `route:list`).
- Configured CORS: `api/*` + `sanctum/csrf-cookie`, origins `*`, credentials disabled.

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
| `Sale` | `sales` | invoice_number, customer_id, cashier_id, branch_id, register_id, subtotal/discount/tax/total/paid/change, payment_method, status, payment_status | `creating` hook auto-generates `INV-YYYYMMDD-0001`; store decrements stock + logs movements |
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
| `HeldSale` | customer, cashier | cashier_id, search hold_number, from_date/to_date |
| `Return` | sale, branch, cashier | sale_id, branch_id, cashier_id, search reason |
| `SaleItem` / `ReturnItem` / `PurchaseItem` / `TransferItem` | product, variant (or related parent) | parent id; per_page default 50 |
| `Purchase` | supplier, branch, warehouse, items | supplier_id, branch_id, warehouse_id, status, payment_status, from_date/to_date, search purchase_number |
| `Expense` | branch, creator | branch_id, category, payment_method, from_date/to_date, search |
| `CashRegisterSession` | register, branch, user | register_id, branch_id, user_id, status |
| `CashTransaction` | session, user | session_id, transaction_type, from_date/to_date |
| `StockMovement` | product, variant, warehouse, user | product_id, warehouse_id, type, from_date/to_date, search reference/note |
| `StockTransfer` | sourceWarehouse, destinationWarehouse, creator, items | source_warehouse_id, destination_warehouse_id, status, from_date/to_date, search transfer_number |
| `Notification` | — | type |
| `AuditLog` | user | user_id, action, module, record |

## 7. Seed data

Run with `php artisan db:seed` (or `php artisan migrate --seed`):
- `RoleSeeder`: `admin` (grant_all=true), `manager`, `cashier`, `accountant`, `viewer`.
- `RolePermissionSeeder`: dotted permission strings (`sales.view`, `sales.create`, `products.view`, `inventory.*`, `registers.open`, `registers.close`, `reports.view`, ...).
- `DatabaseSeeder`: roles, branches, registers, demo users, customers, suppliers, catalog, products + variants, stock, sales/items, purchases, returns, expenses, cash-register sessions + transactions, notifications, audit logs.
- `AdminUserSeeder`: creates `admin@pos.com / 12345678` (System Administrator).
- **Demo users** (password **`Password123!`**): `demo@storemaster.com` (Admin, active), `sreyleap@` / `dara@` (cashiers), `bopha@` (manager), `ronan@` (accountant), `kosal@` (viewer, **inactive**). One cash register session is seeded as `open` for register 2.

To wipe and reseed: `php artisan migrate:fresh --seed`.

## 8. Authentication ⚠️ implemented — know the details

- Sanctum is **fully wired**: login and password recovery/reset routes are public; `auth:sanctum` middleware protects all other API routes.
- `AuthController@login` validates `email`+`password`, checks `Hash::check` against the `users.password_hash` column, rejects inactive accounts (403), updates `last_login_at`, and issues `$user->createToken('pos-api-token')->plainTextToken`. Response: `{message, access_token, token_type, user}`.
- `GET auth/me` → `{user: UserResource}`; `POST auth/logout` revokes the current token.
- `POST auth/change-password` validates `current_password`+`new_password`, checks current password against `password_hash`, updates to new hashed password. Response: `{message, success}`.
- `POST auth/forgot-password` sends a Laravel password-broker notification and returns a generic response that does not reveal whether the email exists. `POST auth/reset-password` validates the emailed token and confirmed password, updates `password_hash`, and revokes existing Sanctum tokens. Reset links target `FRONTEND_URL`; configure that value and production SMTP/mail delivery for deployment.
- **Gotcha:** the `users` table uses `password_hash` (not Laravel's default `password`). `User` overrides `getAuthPassword()` to return `password_hash`, the `password_hash` column has a `hashed` cast, and `UserController` maps `password` → `password_hash` on create/update. Match this pattern when touching users.
- CORS (`config/cors.php`): `api/*` + `sanctum/csrf-cookie`, origins `*`, credentials disabled.
- Sanctum token expiry: `null` (never). Frontend stores the token in `localStorage`/Zustand and sends `Authorization: Bearer <token>` (`src/lib/api.js` in the frontend handles 401→logout, 403→toast).
- Custom RBAC exists in data (roles + permissions + `Role::hasPermission()`) but is **not enforced** on API endpoints — authorization is still the frontend's job (UX-only).
- Full-stack flow: the sibling `Frontend-POS` app is the API client, configured via `VITE_API_URL=http://localhost:8000/api`.

## 9. Known gaps / inconsistencies (fix with care)

1. **Inventory IS now updated** on sales (decrement + movement), received purchases (increment + movement), returns (increment + movement), and stock movements (direct apply) — but transfers/stock-transfers and a few edge paths still need verification; don't assume every path adjusts stock correctly.
2. **`Sale` / `HeldSale` `creating` hooks** auto-generate `invoice_number`/`hold_number`. `HeldSaleController@store` sets `created_at` explicitly (model has `$timestamps = false`); verify numbering and `created_at` are populated as expected for other no-timestamps models.
3. **`HeldSale` + `CashTransaction` models set `$timestamps = false`** — controllers must set `created_at` themselves (both now do).
4. **`Role::users()`** is declared as `BelongsTo` but returns many users → relationship bug.
5. **`ReturnController` ↔ `SaleReturn` model ↔ `SaleReturnResource`** naming mismatch with the `returns` route prefix. `ReturnItem` has `return()` → `SaleReturn`.
6. **`BranchResource`** contains a leftover `warehouses()` relationship method (dead code).
7. **`UserFactory`** still uses the standard `password` field, inconsistent with the DB's `password_hash` column.
8. **No tests** for API behavior. Rate limiting is the default `ThrottleRequests:api` 60/min per user/IP.
9. **No RBAC enforcement server-side** — any authenticated token can hit any endpoint (frontend gates routes by permission only).
10. **`Register` availability is not enforced on `sales.store`** — a sale can be made against a register that has no open cash session.

## 10. Common commands

```bash
composer install
npm install && npm run build        # vite build of the bare scaffold (frontend build lives in ../Frontend-POS)
cp .env.example .env                 # then set DB_DATABASE=Inventory-POS-System, DB_PORT=3307
php artisan key:generate
php artisan migrate --seed           # MySQL: db Inventory-POS-System, port 3307, user root, empty password
php artisan serve                    # serves on :8000 by default — the frontend talks to this
php artisan route:list               # view the 151 api routes
php artisan db:seed                  # re-seed data only
php artisan migrate:fresh --seed     # wipe + reseed from scratch
```

## 11. Golden rules for editing this codebase

- Follow the **existing template pattern** (controller → FormRequest → Resource → model) exactly; mirror a sibling file for any new resource.
- Keep filters/validation consistent (filled() guards, `per_page`, enum `in:` rules, `exists:` FKs).
- **Frontend contract:** the sibling `Frontend-POS` adapter (in `src/services/api.js`) expects **snake_case request bodies** and **camelCases every response key**, and maps filters via `QUERY_FILTER_MAP`. When you rename an attribute/resource or add query filters, keep them compatible (or update the adapter's `RESOURCE_ALIASES`/`RESPONSE_FIELD_MAP`/`QUERY_FILTER_MAP`).
- **Never commit `.env`.** Match the sibling `.env.example` if you change config.
- When adding feature logic (e.g. stock adjustments, auth), prefer the pattern of `RolePermissionController@sync` (transaction, inline validation) and `SaleController@store` (custom response shape) as the closest existing references.
- If behavior must diverge from the common CRUD template, document it here.