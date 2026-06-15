# OrbitPesa — Payment Gateway & Consumer Wallet

A full-stack payment gateway and consumer wallet platform for Kenya, built with PHP 8 and MySQL. Similar in scope to PayHero Kenya, combining a **merchant payment gateway** with a **consumer-facing mobile wallet**.

---

## Features

### Merchant Payment Gateway
- **M-Pesa STK Push** — Safaricom Daraja API integration (sandbox & production)
- **Card Payments** — Stripe-ready checkout (simulated in dev)
- **Payment Links** — Shareable public checkout pages with QR codes
- **Wallet** — Internal merchant ledger with withdrawals
- **Webhooks** — Real-time event delivery with signing secrets
- **API Keys** — Test & live key pairs for REST API access
- **KYC** — Document upload and verification queue
- **Analytics** — Transaction volume charts by channel and day
- **Developer Portal** — Interactive API docs and console

### Consumer Wallet (OrbitPesa Wallet)
- **Onboarding** — Register with national ID or email; mobile-first UI
- **P2P Transfers** — Send money to other wallet users by email, phone or wallet ID
- **Receive Money** — Share wallet ID or QR for incoming transfers
- **Airtime Top-Up** — Safaricom, Airtel, Telkom, Faiba
- **Paybills** — KPLC, Nairobi Water, DSTV, KRA, NHIF, NSSF and manual entry
- **Bank & M-Pesa Out** — Transfer to M-Pesa or bank accounts
- **Transaction History** — Paginated log with type filters
- **Profile** — Edit details, change PIN, view stats

### Admin Console
- **Merchant management** — KYC review, suspend/activate, wallet credit
- **Transaction oversight** — All merchant transactions with export
- **Withdrawal queue** — Approve or reject pending withdrawals
- **Consumer Wallet admin** — Manage wallet users, browse all wallet transactions, adjust balances, suspend accounts
- **Fee configuration** — Per-channel fee rules
- **Activity logs** — Full admin action audit trail
- **Weekly summary emails** — Auto-send digest to active merchants

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.2 |
| Database | MySQL 8 / MariaDB 10.4 |
| Architecture | Custom MVC — single front controller (`index.php`) |
| CSS | Custom (no Bootstrap) — `app.css`, `landing.css`, `wallet.css`, `admin.css` |
| Icons | Font Awesome 6.5 |
| Charts | Chart.js 4 |
| Fonts | Inter (Google Fonts) |
| Server | Apache with `mod_rewrite` (XAMPP for local dev) |

---

## Project Structure

```
OrbitPesa/
├── index.php                  # Front controller — all routing lives here
├── api/v1/                    # REST API (payments, transactions, payment links, wallet)
├── app/
│   ├── config/
│   │   ├── config.php         # App settings, API keys, mail config
│   │   └── database.php       # PDO singleton
│   ├── core/
│   │   ├── functions.php      # Helpers: redirect, flash, csrf, format_amount, time_ago
│   │   ├── Mailer.php         # Email notifications
│   │   └── WebhookDispatcher.php
│   ├── middleware/
│   │   ├── auth.php           # Merchant session auth
│   │   └── admin_auth.php     # Admin session auth
│   └── models/
│       ├── User.php           # Merchant accounts
│       ├── Transaction.php
│       ├── PaymentLink.php
│       ├── Wallet.php         # Merchant ledger
│       ├── ApiKey.php
│       ├── Webhook.php
│       ├── Notification.php
│       ├── Admin.php
│       ├── MpesaAccount.php
│       ├── WalletUser.php     # Consumer wallet users
│       └── WalletTransaction.php
├── assets/
│   ├── css/
│   │   ├── app.css            # Merchant dashboard
│   │   ├── landing.css        # Marketing pages
│   │   ├── wallet.css         # Consumer wallet (mobile-first)
│   │   └── admin.css          # Admin console
│   ├── js/
│   │   ├── app.js
│   │   └── landing.js
│   └── img/favicon.svg
├── database/
│   ├── schema.sql             # Core tables (users, transactions, wallets, …)
│   ├── schema_admin.sql       # Admin tables
│   └── schema_webhooks.sql    # Webhook tables + wallet tables
├── views/
│   ├── layouts/
│   │   ├── app.php            # Merchant dashboard shell
│   │   └── wallet.php         # Consumer wallet shell
│   ├── landing/               # Marketing & public pages
│   ├── auth/                  # Merchant login / register
│   ├── dashboard/             # Merchant dashboard pages
│   ├── wallet/                # Consumer wallet pages
│   ├── pay/                   # Public payment link checkout
│   ├── developers/            # API docs & developer console
│   └── admin/                 # Admin console pages
└── .htaccess                  # mod_rewrite rules
```

---

## Local Setup

### Requirements
- PHP 8.0+
- MySQL 8 or MariaDB 10.4+
- Apache with `mod_rewrite` enabled (XAMPP recommended for Windows)

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/sammy001-svg/OrbitPesa.git
cd OrbitPesa
```

**2. Create the database**
```sql
CREATE DATABASE orbitpesa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**3. Run the schema files in order**
```bash
mysql -u root orbitpesa < database/schema.sql
mysql -u root orbitpesa < database/schema_admin.sql
mysql -u root orbitpesa < database/schema_webhooks.sql
```

**4. Configure the app**

Edit `app/config/config.php`:
```php
define('APP_URL', 'http://localhost/OrbitPesa');  // adjust to your server
```

Edit `app/config/database.php` if your MySQL credentials differ from the defaults:
```php
$host   = 'localhost';
$dbname = 'orbitpesa';
$user   = 'root';
$pass   = '';
```

**5. Configure Apache**

Place the project in your web root (e.g. `C:\xampp\htdocs\OrbitPesa` on Windows) and ensure `mod_rewrite` is enabled. The `.htaccess` file handles all URL rewriting.

**6. Create a demo wallet user (optional)**

Visit `http://localhost/OrbitPesa/wallet/register` to create a wallet account, or seed one manually via the registration form.

---

## Demo Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@orbitpesa.com | password |
| Merchant | demo@orbitpesa.com | password |
| Wallet user | demo@orbitpesa.com | Demo1234 |

> **Note:** These are development credentials only. Change all passwords before any public deployment.

---

## Key URLs

| Page | URL |
|---|---|
| Landing page | `/` |
| Merchant login | `/login` |
| Merchant dashboard | `/dashboard` |
| Consumer wallet | `/wallet` |
| Wallet login | `/wallet/login` |
| Admin console | `/admin` |
| Developer docs | `/developers/docs` |
| API base | `/api/v1/` |

---

## REST API

All API endpoints require an `Authorization: Bearer <api_key>` header.

```
POST   /api/v1/payments/initiate      # Initiate M-Pesa STK Push
GET    /api/v1/transactions           # List transactions
GET    /api/v1/transactions/{ref}     # Get transaction by reference
POST   /api/v1/payment-links         # Create payment link
GET    /api/v1/payment-links/{slug}  # Get payment link details
```

Full interactive documentation is available at `/developers/docs`.

---

## Design System

| Token | Value |
|---|---|
| Primary Green | `#158347` |
| Navy Blue | `#0D1B3E` |
| Font | Inter |
| UI framework | Custom CSS (no Bootstrap) |

The consumer wallet UI is **mobile-first** with a phone-frame preview on desktop (max 430 px, centered, dark navy background).

---

## Roadmap

- [ ] Live M-Pesa Daraja API integration
- [ ] Africa's Talking airtime API
- [ ] Real paybill processing
- [ ] 2FA / device verification for wallet
- [ ] Merchant ↔ wallet payments (pay merchants from wallet)
- [ ] Savings pockets & group wallets (chama)
- [ ] PDF statement export
- [ ] Docker setup

---

## License

MIT — feel free to fork and adapt for your own projects.
