# Apotek Alifa

Apotek Alifa is a native PHP pharmacy e-commerce web application for managing online medicine sales, customer shopping flows, order processing, payment proof uploads, and admin-side pharmacy operations.

This project was built as a portfolio project to demonstrate practical PHP web development skills without relying on a full-stack framework. It focuses on server-side PHP logic, MySQL database operations, prepared statements, authentication, cart management, checkout transactions, user/admin workflows, and structured project organization.

## Project Highlights

- Built with **Native PHP**, **MySQL**, and **MySQLi**.
- Implements customer and admin workflows in one pharmacy e-commerce system.
- Uses session-based authentication for login, registration, logout, and role handling.
- Supports product catalog browsing, product detail pages, shopping cart, checkout, order creation, and payment proof upload.
- Provides admin-side product management, order management, user management, and dashboard statistics.
- Applies prepared statements for safer database queries.
- Uses transaction handling during checkout to keep order creation, order items, payment record creation, stock deduction, and cart clearing consistent.
- Uses environment-based database configuration through `.env` and `.env.example`.

## Tech Stack

| Area | Technology |
|---|---|
| Backend | Native PHP |
| Database | MySQL, MySQLi |
| Frontend | HTML, CSS, SCSS, JavaScript |
| Authentication | PHP Sessions, password hashing, password verification |
| Database Access | MySQLi prepared statements |
| Configuration | `.env`-based database configuration |
| Development Tools | Git, GitHub, VS Code |

## Main Features

### Public Storefront

- Landing page for pharmacy branding.
- Product catalog page.
- Product detail page.
- Product search and category filtering.
- Pagination for product listing.
- Product image display.
- Cart counter for authenticated users.

### Authentication

- User registration.
- User login using username or email.
- Password hashing with `password_hash`.
- Password verification with `password_verify`.
- Session-based login state.
- Logout flow.
- Login-required route protection.
- Role-based admin access checks.

### Customer Features

- Browse pharmacy products.
- Add products to cart.
- Update cart item quantity.
- Remove products from cart.
- View cart total and cart item count.
- Checkout from cart items.
- Submit recipient details, address, email, phone number, and notes.
- Upload payment proof.
- View pending orders.
- View order history.
- View order details and payment status.

### Cart Management

- Adds products to the authenticated user's cart.
- Updates quantity when the product already exists in cart.
- Stores product price at the time the item is added.
- Calculates total cart value.
- Clears cart after successful order creation.
- Uses prepared statements for cart insert, update, delete, and retrieval operations.

### Checkout and Order Processing

- Creates an order from current cart items.
- Calculates total price from cart quantity and stored item price.
- Inserts order records into the `pesanan` table.
- Inserts ordered items into the `barang_pesanan` table.
- Creates a pending payment record in the `pembayaran` table.
- Deducts product stock during checkout with stock availability validation.
- Uses database transaction handling to prevent partially created orders.
- Clears the user's cart after successful checkout.

### Payment Proof Upload

- Validates uploaded payment proof file type.
- Supports JPG, JPEG, PNG, and PDF files.
- Limits upload size to 5MB.
- Stores uploaded payment proof with a generated filename.
- Updates payment records after upload.
- Verifies order ownership before accepting payment proof upload.

### Admin Dashboard

- Shows order statistics.
- Calculates total revenue from successful orders.
- Displays pending, successful, failed, and total orders.
- Shows recent order records.
- Restricts admin pages to users with admin role.

### Admin Product Management

- List products with pagination.
- Search products.
- Filter products by category.
- Add new products.
- Edit existing products.
- Delete products when they do not already have order records.
- Upload product images.
- Validate image type.
- Remove old product images when updating product data.

### Admin Order Management

- View all orders with pagination.
- Search orders by recipient, email, phone number, order ID, or username.
- Filter orders by status.
- View order details.
- Update order status.
- Store approved order history in `riwayat_pesanan`.
- Save a JSON snapshot of ordered items when order status is updated.

### Admin User Management

- List users with pagination.
- Search users by username, email, or full name.
- Filter users by role.
- Add new users.
- Edit user data.
- Delete users only when they do not have order history.
- Validate username, email, password, and duplicate records.
- Hash user passwords before storage.

## What This Project Demonstrates

This project is useful for PHP Programmer and Backend Web Developer portfolio screening because it shows hands-on experience with real application flows. This project demonstrates practical application flows beyond static page implementation.

### Native PHP Application Development

- Organized a multi-page PHP application using reusable function files.
- Separated core logic into dedicated files such as authentication, cart, checkout, product, order, and user functions.
- Implemented backend workflows for pharmacy product sales, cart management, checkout, payment upload, order status updates, and admin operations.

### Database-Driven Business Logic

- Uses relational database tables for users, products, categories, carts, orders, order items, payments, and order history.
- Implements joins to retrieve product, category, order, payment, and user data.
- Uses pagination, search, and filtering to manage data-heavy screens.
- Applies stock deduction and validation to prevent orders from exceeding available inventory.

### Security and Data Handling

- Uses prepared statements for database operations.
- Uses password hashing and verification for authentication.
- Uses session checks to protect authenticated pages.
- Uses role checks for admin-only features.
- Moves database credentials into environment-based configuration instead of hardcoding them directly in source files.

### Transaction-Oriented Checkout

- Wraps checkout logic inside database transaction handling.
- Creates order, order items, payment record, stock update, and cart clearing as one coordinated workflow.
- Rolls back changes if an error occurs during checkout.

### Portfolio-Relevant PHP Skills

- Native PHP
- MySQL and MySQLi
- Prepared statements
- Authentication and session management
- CRUD operations
- File upload validation
- Shopping cart logic
- Checkout workflow
- Stock management
- Admin dashboard logic
- Search and pagination
- Role-based access control
- Environment-based configuration

## Repository Structure

```txt
apotek-alifa/
├── assets/
│   ├── img/
│   ├── js/
│   ├── scss/
│   └── css/
├── auth/
│   ├── login.php
│   └── register.php
├── dashboard/
│   └── admin/user dashboard pages
├── database/
│   └── apotek_alifa.sql
├── layouts/
│   └── shared layout pages
├── auth_functions.php
├── cart_functions.php
├── checkout_functions.php
├── connect.php
├── order_functions.php
├── product_functions.php
├── user_functions.php
├── landing.php
├── shop.php
├── show.php
├── cart.php
├── payments.php
├── process_checkout.php
└── .env.example
```

## Database Tables Used

The application logic references the following main database tables:

| Table | Purpose |
|---|---|
| `pengguna` | Stores user and admin accounts |
| `obat` | Stores medicine/product data |
| `kategori` | Stores product categories |
| `keranjang` | Stores user cart items |
| `pesanan` | Stores customer orders |
| `barang_pesanan` | Stores ordered product items |
| `pembayaran` | Stores payment proof data |
| `riwayat_pesanan` | Stores approved/processed order history snapshots |

## Installation

### Prerequisites

Make sure your environment has the following installed:

- PHP 8 or higher
- MySQL or MariaDB
- Apache/Nginx or a local development stack such as XAMPP, Laragon, or MAMP
- Git

### Setup Steps

1. Clone this repository.

```bash
git clone https://github.com/zyalwfie/apotek-alifa.git
cd apotek-alifa
```

2. Copy the environment example file.

```bash
cp .env.example .env
```

3. Configure your database connection in `.env`.

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=apotek_alifa
```

4. Import the database schema and seed data.

```bash
mysql -u root -p < database/apotek_alifa.sql
```

This will create the `apotek_alifa` database, all required tables, and seed data including a default admin account and sample products.

Default admin credentials:

```
Username : admin
Password : admin123
```

6. Place the project inside your local web server directory.

Example for XAMPP:

```txt
htdocs/apotek-alifa
```

7. Open the project in your browser.

```txt
http://localhost/apotek-alifa
```

## Portfolio Relevance

This project is suitable for a PHP Programmer portfolio because it demonstrates:

- Native PHP web application development.
- MySQL database-driven application logic.
- MySQLi prepared statements.
- Authentication and session management.
- User and admin role separation.
- CRUD features for products and users.
- Product search, category filtering, and pagination.
- Shopping cart and checkout flow.
- Order processing with transaction handling.
- Payment proof upload with file validation.
- Stock management.
- Admin dashboard statistics.
- Environment-based database configuration.

## Recommended Improvements

To make this repository even stronger for job applications, the next improvements should be:

- Add screenshots for landing page, shop page, cart page, checkout page, admin dashboard, product management, and order management.
- Add REST API endpoints for products, carts, and orders.
- Improve folder organization using a cleaner MVC-like structure.
- Add server-side validation helpers for repeated validation logic.
- Add deployment instructions for shared hosting or Linux server environments.
- Add basic automated tests for cart, checkout, and order status workflows.

## Author

**Ziyat Al Wafi**  
GitHub: [github.com/zyalwfie](https://github.com/zyalwfie)
