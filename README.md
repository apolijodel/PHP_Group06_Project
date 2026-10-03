# MarkMe — Customizable Bookmark E-Commerce System

A PHP and MySQL web application for a small studio that sells personalized
bookmarks. Customers browse a catalog, design their own bookmark by picking a
shape, a design, a photo and a short line of text, save designs to return to,
and place orders. Administrators and staff manage the catalog, inventory and
orders from a separate back office, with role-based access, an audit trail and
an approval queue for anything uploaded to the design studio.

---

## Group Members

| # | Name | GitHub |
|---|------|--------|
| 1 | Jodel Apoli | [@apolijodel](https://github.com/apolijodel) |
| 2 | *(to be completed)* | *(to be completed)* |
| 3 | *(to be completed)* | *(to be completed)* |


> Group leader: please fill in each member's name and GitHub username before
> submitting. Every member also needs at least one commit of their own — being
> listed here is not the same as contributing, and the instructor checks the
> commit history.

---

## Project Description

MarkMe is a storefront and back office in one project.

Shoppers register, confirm their account, sign in, browse bookmarks by category,
open the design studio to customize a product, keep a cart across visits, and
check out with cash on delivery or bank transfer. They can save a design and
reuse it later, and follow an order through to delivery.

Staff and administrators work in a separate panel. Administrators manage
products, categories, shapes, designs, customers, staff and system settings, and
review every uploaded shape or design before customers can use it. Staff get a
narrower panel covering the catalog, inventory, customers and orders. What each
role may do is decided by capabilities checked on the server, so a restricted
user cannot reach a screen or an action by typing its URL.

---

## Technologies Used

- **PHP 8.2** — no framework, no Composer
- **MySQL / MariaDB** — accessed through PDO with prepared statements
- **HTML5**
- **CSS** — a design system split across nine stylesheets, with a dark theme
- **JavaScript** — progressive enhancement only; every feature still works without it
- **Bootstrap 5.3** — layout and components, loaded from a CDN
- **Apache (XAMPP)**

---

## Features

**Shopping**
- Product catalog with category filtering and search
- Design studio: shape, design, photo upload and custom text, with a live preview
- Saved designs that can be reused or edited
- Cart that persists between visits, with quantity changes that update in place
- Checkout with server-calculated totals and stock validation
- Order history and order tracking

**Back office**
- Dashboard with sales, stock and order figures
- Product, category, shape and design management (full CRUD)
- Inventory with low-stock tracking and restocking
- Order management with status updates
- Customer, staff and administrator management
- Reports

**Security**
- Registration with CAPTCHA and email account activation
- Two-factor authentication using an authenticator app (TOTP, RFC 6238)
- Password policy: 12 characters with upper case, lower case, a number and a symbol
- Password expiry and reuse history
- Account lockout after three failed sign-in attempts, with administrator unlock
- Session timeout with automatic sign-out
- Role-based access control across three roles, enforced on the server
- Prepared statements, output escaping and CSRF tokens throughout
- Secure uploads: type, size and content validation, with an approval queue
- Audit trail of sign-ins, sign-outs and every create, update and delete
- Privacy policy and cookie consent

---

## Database

**Database name:** `markme_db`
**SQL export:** [`database/markme_database.sql`](database/markme_database.sql)

One file creates the whole schema and loads the demonstration data: 16 tables
covering accounts, the catalog, carts, orders, saved designs, settings, the
activity log and the security tables (account lockouts, activation tokens and
password history).

> The script **drops and recreates** `markme_db`. Do not run it against data you
> want to keep.

---

## Installation / Setup

1. **Install XAMPP** and start **Apache** and **MySQL**.

2. **Put the project where Apache can serve it**, so it sits at
   `C:\xampp\htdocs\markme` (or your equivalent `htdocs` path).

   ```bash
   git clone https://github.com/apolijodel/PHP_Group06_Project.git markme
   ```

3. **Create the database.** In phpMyAdmin choose *Import* and select
   `database/markme_database.sql`, or from a terminal:

   ```bash
   mysql -u root < database/markme_database.sql
   ```

4. **Create the configuration file.** `config/config.php` is deliberately not in
   the repository, because it holds database settings. Copy the example and edit
   it if your MySQL user or password differ from the XAMPP defaults:

   ```bash
   cp config/config.example.php config/config.php
   ```

5. **Open the site** at <http://localhost/markme>.

### Demonstration accounts

| Role | Username | Password |
|------|----------|----------|
| Administrator | `admin` | `Admin@123456` |
| Staff | `staff` | `Staff@123456` |
| Customer | `customer` | `Customer@123` |

These are placeholder accounts for checking the project. Change them before
serving the site anywhere other than localhost.

### Optional integrations

These are built and switched on from `config/config.php`. With the values left
blank the application says so rather than pretending the feature ran.

| Setting | Purpose | Without it |
|---------|---------|------------|
| `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` | Google reCAPTCHA on registration | A self-hosted image CAPTCHA is used instead |
| `SMTP_*` | Account-activation email | The message is written to `storage/outbox` instead of being sent |
| `AV_SCANNER` | ClamAV scanning of uploads | Uploads are recorded as **not scanned**, never as clean |
| `FORCE_HTTPS` | Marks session cookies `Secure` | Leave `false` on localhost; set it once the site is served over HTTPS |

---

## Project Structure

```
PHP_Group06_Project/
│
├── admin/              Administrator panel
├── staff/              Staff panel
├── customer/           Storefront, cart, checkout and design studio
├── auth/               Activation, MFA and CAPTCHA endpoints
├── includes/           Shared PHP: auth, security, helpers, shared views
├── assets/
│   ├── css/            Design system stylesheets
│   └── js/             script.js
├── uploads/            Product, shape, design and customer images
├── database/
│   └── markme_database.sql
├── config/
│   └── config.example.php
├── index.php
└── README.md
```
