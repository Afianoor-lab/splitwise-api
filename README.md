# Splitwise-Like Expense Sharing API (Laravel + MongoDB)

A RESTful backend API built with Laravel and MongoDB for group expense management, supporting dynamic splitting algorithms and automated debt simplification.

## Prerequisites
- PHP >= 8.1
- Composer
- MongoDB Server & PHP `mongodb` extension

## Setup & Installation

1. **Clone the repository:**
   ```bash
   git clone <your-repo-url>
   cd splitwise-app


2. Install Dependencies:
Bash
composer install

3.  Environment Configuration:
cp .env.example .env
php artisan key:generate

Set your MongoDB credentials in .env:
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=splitwise_db

4. Run Migrations & Seeders:
php artisan migrate:fresh --seed

5. Start Development Server:
php artisan serve

Key Features:
Custom Token Auth: Native bin2hex(random_bytes(32)) implementation via custom middleware.

Splits Supported: Equal, Exact, and Percentage splits.

Debt Simplification: Graph-based debt reduction logic.

Architecture: Dedicated Service Layers, Custom Exceptions, and Form Requests.


Running Tests:
php artisan test

Postman Collection
