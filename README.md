<div align="center">

# YieldVault

### FinTech Investment & Wallet Management API

A Laravel backend for wallet operations, investment workflows, referral commissions, and secure account verification.

**Wallet Ledger** · **Investment & ROI** · **Referral Network** · **Email 2FA** · **KYC**

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![JWT](https://img.shields.io/badge/Auth-JWT%20%7C%20Sanctum-111827?style=flat-square)
![Redis](https://img.shields.io/badge/Queue%20%26%20Cache-Redis-DC382D?style=flat-square&logo=redis&logoColor=white)

</div>

---

## Project Overview

**YieldVault** is a REST API backend built with **Laravel 11** and **PHP 8.2+**. It supports core workflows for a financial and investment platform, including wallet and transaction management, investment tracking, ROI-related processing, multi-level referrals, KYC review, and account security.

## Core Features

| Area | What it does |
|---|---|
| **Wallet & Transactions** | Manages wallet records, transaction history, deposits, and withdrawal requests |
| **Investment Management** | Handles investment packages, user investments, and investment logs |
| **ROI Processing** | Includes scheduled/background processing workflows for ROI-related operations |
| **Referral Network** | Supports referral links, levels, referral settings, and commission-related logic |
| **Authentication & 2FA** | JWT login flow with an additional email OTP verification step |
| **KYC** | Supports document submission and manager review workflows |
| **Daily Engagement** | Daily tasks, completion history, and streak-related activity |
| **Admin / Manager APIs** | Provides manager endpoints for dashboards, wallet accounts, and deposit/withdrawal reviews |
| **Notifications** | Firebase Cloud Messaging token management and notification endpoints |
| **Access Control** | Role and permission management using Spatie Laravel Permission |

## Security Highlight: Two-Factor Authentication

YieldVault includes an email-based **Two-Factor Authentication (2FA)** flow:

- Generates a six-character alphanumeric verification code.
- Stores a hash of the OTP rather than the plain-text code.
- Sets a **5-minute** OTP expiration.
- Invalidates previous unused codes when a new code is generated.
- Uses a temporary JWT while login verification is pending.
- Issues the regular authentication token after successful OTP verification.
- Tracks failed 2FA verification attempts.
- Provides endpoints to check, enable, verify, and disable 2FA.

## Technology Stack

| Technology | Purpose |
|---|---|
| **PHP 8.2+ / Laravel 11** | Backend application and REST API |
| **MySQL** | Relational data storage |
| **JWT Auth** | API authentication and temporary 2FA login token |
| **Laravel Sanctum** | Token / session authentication support |
| **Redis / Laravel Queues** | Queue and cache infrastructure |
| **Spatie Laravel Permission** | Role-based access control |
| **Firebase (Kreait)** | Firebase integration and push notification support |
| **Laravel Socialite** | Social authentication integration |
| **AWS S3 Flysystem** | S3-compatible file storage integration |
| **Pest / PHPUnit** | Automated test suite |

## Backend Engineering

- **Domain-focused modules:** separate controllers, models, services, requests, and API routes for major application areas.
- **Financial record keeping:** dedicated wallet, wallet transaction, investment, and investment log data models.
- **Asynchronous operations:** Laravel queue infrastructure for background work.
- **Authorization:** role and permission-based access management.
- **Request validation:** dedicated Form Request classes for API input validation.

## Run Locally

### Requirements

- PHP 8.2+
- Composer
- MySQL
- Redis (where required by your local queue/cache configuration)

### Installation

```bash
# Clone the repository
git clone https://github.com/your-username/yieldvault-backend.git
cd yieldvault-backend

# Install PHP dependencies
composer install

# Create environment configuration
cp .env.example .env
php artisan key:generate
```

Configure your database and other required services in `.env`, then run:

```bash
php artisan migrate --seed
php artisan serve
```

The local application will be available at `http://127.0.0.1:8000`.

## Run Tests

```bash
php artisan test
```

The repository includes Pest/PHPUnit test configuration for unit and feature tests.

## API Areas

The versioned API routes are organized around these functional areas:

- Authentication and profile
- Two-factor authentication
- Wallets and transactions
- Investments and packages
- Referrals and levels
- KYC submission and review
- Daily tasks
- Notifications
- Manager dashboard and financial request reviews

See `routes/api.php` for the available endpoints and middleware.

## License

See the [`LICENSE`](LICENSE) file for license details.

---

<div align="center">

**YieldVault** · Laravel FinTech Backend

</div>
