# NexusB2B — Wholesale Order & Credit Management

A Laravel 11 application for B2B wholesale ordering with volume-based pricing and
enforced credit limits. Sales representatives build and submit draft orders;
credit managers approve, reject or dispatch them — all backed by atomic,
lock-guarded domain actions.

## Features

- **Two roles with enforced RBAC**
  - *Sales Representative* — create draft orders for active customers, add/update/remove
    line items, submit orders for credit review, dispatch their own approved orders.
  - *Credit Manager* — review queue, approve (releases stock) or reject orders with a
    mandatory reason, dispatch approved orders.
- **Volume-tiered pricing** applied automatically per line item:
  | Quantity | Tier | Discount |
  |---|---|---|
  | 1–49 | Standard | 0% |
  | 50–99 | Bulk | 5% |
  | 100+ | Enterprise | 10% |
- **Credit control** — an order can only be submitted/approved while the customer is
  active and the order total fits within `credit_limit − outstanding_balance`.
  A fast pre-check runs in the Form Request; the authoritative check runs again inside
  the approval transaction under a row lock.
- **Atomic order lifecycle** — `Draft → Submitted → Approved → Dispatched`
  (or `→ Rejected`). Approvals decrement stock and increment the customer balance
  inside a single `DB::transaction()` with deterministic `lockForUpdate()` ordering.
- **Full audit trail** — who approved/rejected/dispatched and when, plus rejection reasons.

## Tech stack

- PHP 8.2+, Laravel 11
- Blade + Tailwind CSS 3 (Vite build)
- SQLite by default; MySQL 8 recommended for true row-level locking

## Requirements

- PHP 8.2 or newer (with `pdo_sqlite` or `pdo_mysql`)
- [Composer](https://getcomposer.org)
- Node.js 18+ and npm

## Setup

```bash
# 1. PHP dependencies
composer install

# 2. Environment
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate

# 3. Database (SQLite by default — creates an empty file)
#    Windows: type nul > database/database.sqlite   (or: ni database/database.sqlite)
touch database/database.sqlite

# 4. Schema + demo data
php artisan migrate:fresh --seed

# 5. Front-end assets
npm install
npm run build

# 6. Serve
php artisan serve
```

Then open <http://localhost:8000>.

### Using MySQL 8 instead of SQLite

Edit `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexusb2b
DB_USERNAME=root
DB_PASSWORD=
```

Then re-run `php artisan migrate:fresh --seed`. MySQL is required for the row-level
locking guarantees exercised by `OrderConcurrencyTest`.

## Demo credentials

Seeded by `php artisan migrate:fresh --seed` (password is `password` for all):

| Role | Email | Password |
|---|---|---|
| Credit Manager | `manager@nexusb2b.test` | `password` |
| Sales Representative | `rep@nexusb2b.test` | `password` |
| Sales Representative | `rep2@nexusb2b.test` | `password` |

The seeder also creates 5 customers (including a near-limit and an inactive account),
8 products (one discontinued) and 6 orders spanning every status — including one
`Submitted` order already waiting in the manager's review queue.

## Routes

| Method | URI | Action |
|---|---|---|
| GET | `/login`, POST `/login`, POST `/logout` | Session auth |
| GET | `/dashboard` | Role-aware landing |
| GET | `/products` | Catalogue |
| GET | `/customers`, `/customers/{customer}` | Accounts & live credit |
| GET/POST | `/orders`, `/orders/create` | List / create draft |
| GET | `/orders/{order}` | Detail + lifecycle |
| POST/PUT/DELETE | `/orders/{order}/items[/{product}]` | Draft line-item CRUD |
| POST | `/orders/{order}/submit` | Submit for credit review |
| POST | `/orders/{order}/review` | Approve / reject |
| POST | `/orders/{order}/dispatch` | Mark dispatched |

## Architecture

Business logic lives outside controllers, which stay thin (authorize → delegate → redirect).

```
app/
├── Actions/Orders/          # One class per lifecycle operation (transactional)
├── Enums/                   # UserRole, OrderStatus, CustomerStatus, VolumeTier
├── Exceptions/              # Domain exceptions (mapped to flash errors centrally)
├── Http/
│   ├── Controllers/         # Thin HTTP layer
│   └── Requests/            # Validation + authorization + credit pre-check
├── Models/                  # Eloquent models, casts and relations
├── Policies/                # OrderPolicy, CustomerPolicy, ProductPolicy
├── Services/                # PricingCalculatorService, OrderCreditEvaluator, OrderTotalCalculator
└── ValueObjects/            # LineItemPricing (readonly)
```

Domain exceptions are rendered centrally (`bootstrap/app.php`) to a redirect with a
flash message, so controllers remain free of `try/catch`.

## Testing

```bash
php artisan test          # or: composer test
```

- `tests/Unit` — pricing engine and volume-tier boundaries.
- `tests/Feature` — authentication, full order lifecycle, credit rules, inventory
  rollback, RBAC/403 authorization and pricing integration.
- `OrderConcurrencyTest` self-skips unless the active driver supports row locks
  (MySQL/Postgres), documenting where the concurrency guarantee is verified.

Tests run against an in-memory SQLite database (`phpunit.xml`), so they never touch
your development data.

## Code style

```bash
composer pint             # or: vendor/bin/pint
```

## Known considerations

- SQLite ignores `SELECT ... FOR UPDATE`; use MySQL 8 to verify concurrency safety.
- With Laravel 11.57 a `brick/math` deprecation notice can appear from the framework's
  `decimal` cast internals; it does not affect behaviour.
- Credit-limit "temporary holds / overrides" are not modelled as there is no such
  state in the current schema.
