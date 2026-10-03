-- MarkMe: Customizable Bookmark E-Commerce System
-- Database schema + demonstration seed data.

-- ---------------------------------------------------------------------
-- WARNING: this script RESETS the database. The DROP below deletes every
-- existing MarkMe record — accounts, orders, carts and saved designs —
-- and rebuilds the schema with the demonstration seed data further down.
-- Run it to prepare a clean demo, never against data you want to keep.
--
-- Without the DROP, re-importing over an existing markme_db fails with
-- "Table 'users' already exists", so a reset was not actually possible.
-- ---------------------------------------------------------------------
DROP DATABASE IF EXISTS markme_db;
CREATE DATABASE markme_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE markme_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- users: customer and administrator accounts
-- ---------------------------------------------------------------------
CREATE TABLE users (
    user_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role            ENUM('customer', 'staff', 'admin') NOT NULL DEFAULT 'customer',
    username        VARCHAR(50)  NOT NULL UNIQUE,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    full_name       VARCHAR(150) NOT NULL,
    avatar_path     VARCHAR(255) NULL,
    contact_number  VARCHAR(30)  NULL,
    address         VARCHAR(255) NULL,
    city            VARCHAR(100) NULL,
    province        VARCHAR(100) NULL,
    postal_code     VARCHAR(10)  NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Sign-in security. The TOTP secret is the shared key behind two-factor
    -- authentication, so it is read only by the code that verifies a code and
    -- is never selected into a page. activated_at is NULL until the account
    -- has confirmed its email; password_changed_at drives expiry.
    mfa_secret          VARCHAR(64) NULL,
    mfa_enabled         TINYINT(1)  NOT NULL DEFAULT 0,
    mfa_confirmed_at    TIMESTAMP   NULL,
    password_changed_at TIMESTAMP   NULL,
    activated_at        TIMESTAMP   NULL,
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- categories: product category records
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    category_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    description     VARCHAR(255) NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- shapes: admin-managed bookmark shape options
-- ---------------------------------------------------------------------
CREATE TABLE shapes (
    shape_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    image_path      VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- designs: admin-managed template designs
-- ---------------------------------------------------------------------
CREATE TABLE designs (
    design_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    image_path      VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- products: product information, price, stock, image, category reference
-- ---------------------------------------------------------------------
CREATE TABLE products (
    product_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED NOT NULL,
    name            VARCHAR(150) NOT NULL,
    description     TEXT NULL,
    price           DECIMAL(10,2) NOT NULL,
    stock_quantity  INT UNSIGNED NOT NULL DEFAULT 0,
    low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5,
    image_path      VARCHAR(255) NOT NULL,
    is_customizable TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories(category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_products_category (category_id),
    INDEX idx_products_name (name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- carts: one active cart per customer
-- ---------------------------------------------------------------------
CREATE TABLE carts (
    cart_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- cart_items: products (and chosen customization) inside a cart
-- ---------------------------------------------------------------------
CREATE TABLE cart_items (
    cart_item_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id           INT UNSIGNED NOT NULL,
    product_id        INT UNSIGNED NOT NULL,
    quantity          INT UNSIGNED NOT NULL DEFAULT 1,
    shape_id          INT UNSIGNED NULL,
    design_id         INT UNSIGNED NULL,
    custom_text       VARCHAR(120) NULL,
    custom_image_path VARCHAR(255) NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id)
        REFERENCES carts(cart_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id)
        REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_items_shape FOREIGN KEY (shape_id)
        REFERENCES shapes(shape_id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_cart_items_design FOREIGN KEY (design_id)
        REFERENCES designs(design_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_cart_items_cart (cart_id),
    INDEX idx_cart_items_product (product_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- orders: order header, customer, address, total, status, timestamps
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    order_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           INT UNSIGNED NOT NULL,
    full_name         VARCHAR(150) NOT NULL,
    contact_number    VARCHAR(30)  NOT NULL,
    delivery_address  VARCHAR(255) NOT NULL,
    payment_method    ENUM('Cash on Delivery', 'Bank Transfer') NOT NULL DEFAULT 'Cash on Delivery',
    total_amount      DECIMAL(10,2) NOT NULL,
    status            ENUM('Pending', 'Processing', 'On Shipping', 'Completed', 'Cancelled')
                          NOT NULL DEFAULT 'Pending',
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- order_items: products, quantities, and recorded unit prices per order
-- Name/price fields are snapshots taken at order time so historical
-- orders stay accurate even if the product/shape/design is later edited.
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    order_item_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id              INT UNSIGNED NOT NULL,
    product_id            INT UNSIGNED NULL,
    product_name_snapshot VARCHAR(150) NOT NULL,
    unit_price            DECIMAL(10,2) NOT NULL,
    quantity              INT UNSIGNED NOT NULL,
    shape_id              INT UNSIGNED NULL,
    shape_name_snapshot   VARCHAR(100) NULL,
    design_id             INT UNSIGNED NULL,
    design_name_snapshot  VARCHAR(100) NULL,
    custom_text           VARCHAR(120) NULL,
    custom_image_path     VARCHAR(255) NULL,
    line_total            DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
        REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id)
        REFERENCES products(product_id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_shape FOREIGN KEY (shape_id)
        REFERENCES shapes(shape_id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_design FOREIGN KEY (design_id)
        REFERENCES designs(design_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_order_items_order (order_id),
    INDEX idx_order_items_product (product_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- feedback_messages: contact-us / feedback submissions (open to guests)
-- ---------------------------------------------------------------------
CREATE TABLE feedback_messages (
    feedback_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    message         TEXT NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- saved_designs: customer-owned reusable bookmark designs.
-- Normalized against shapes/designs/products rather than copying their
-- names, and removed with the owning user.
-- ---------------------------------------------------------------------
CREATE TABLE saved_designs (
    saved_design_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           INT UNSIGNED NOT NULL,
    product_id        INT UNSIGNED NULL,
    design_name       VARCHAR(100) NOT NULL,
    shape_id          INT UNSIGNED NULL,
    design_id         INT UNSIGNED NULL,
    custom_text       VARCHAR(120) NULL,
    custom_image_path VARCHAR(255) NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_saved_designs_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_saved_designs_product FOREIGN KEY (product_id)
        REFERENCES products(product_id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_saved_designs_shape FOREIGN KEY (shape_id)
        REFERENCES shapes(shape_id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_saved_designs_design FOREIGN KEY (design_id)
        REFERENCES designs(design_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_saved_designs_user (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- settings: key/value store backing the admin Settings screen, so store
-- information is persisted rather than hardcoded in the markup.
-- ---------------------------------------------------------------------
CREATE TABLE settings (
    setting_key   VARCHAR(60) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- activity_log: the audit trail behind the admin dashboard. One row per
-- notable event from either side of the site. The actor's name and role are
-- snapshotted so an entry stays readable after the account is renamed or
-- deleted; user_id is kept for linking and goes NULL instead.
-- ---------------------------------------------------------------------
CREATE TABLE activity_log (
    log_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NULL,
    actor_role   ENUM('customer','staff','admin','guest') NOT NULL DEFAULT 'guest',
    actor_name   VARCHAR(100) NOT NULL,
    action       VARCHAR(50) NOT NULL,
    module       VARCHAR(40) NULL,
    entity_type  VARCHAR(30) NULL,
    entity_id    INT UNSIGNED NULL,
    summary      VARCHAR(255) NOT NULL,
    -- Recorded with the event so an entry can be traced to where it came
    -- from, not just to who it belongs to.
    ip_address   VARCHAR(45)  NULL,
    user_agent   VARCHAR(255) NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_activity_created (created_at DESC),
    INDEX idx_activity_role (actor_role),
    INDEX idx_activity_module (module),
    INDEX idx_activity_action (action),
    INDEX idx_activity_entity (entity_type, entity_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- account_locks: failed sign-in attempts, and the lock they lead to
--
-- One row per account rather than one per attempt: the question being asked
-- is "is this account locked right now", not "list every attempt ever made".
-- The audit trail already records the attempts.
-- ---------------------------------------------------------------------
CREATE TABLE account_locks (
    lock_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    username        VARCHAR(50)  NOT NULL,
    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    last_attempt_at TIMESTAMP    NULL,
    locked_at       TIMESTAMP    NULL,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_account_locks_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_account_locks_user (user_id),
    INDEX idx_account_locks_locked (locked_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- email_verification_tokens: one-time links that activate an account
--
-- Only the SHA-256 of the token is stored. A stolen copy of this table does
-- not let anyone activate an account, because the value that was emailed
-- cannot be recovered from its hash.
-- ---------------------------------------------------------------------
CREATE TABLE email_verification_tokens (
    token_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash CHAR(64)     NOT NULL,
    purpose    ENUM('activation') NOT NULL DEFAULT 'activation',
    expires_at DATETIME     NOT NULL,
    used_at    TIMESTAMP    NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evt_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_evt_hash (token_hash),
    INDEX idx_evt_user (user_id),
    INDEX idx_evt_expires (expires_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- password_history: previous hashes, so a password cannot be reused
--
-- Hashes only, exactly as in users.password_hash — this table makes reuse
-- detectable without ever storing a password that could be read back.
-- ---------------------------------------------------------------------
CREATE TABLE password_history (
    history_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pwhist_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_pwhist_user (user_id, created_at)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Seed data
-- ---------------------------------------------------------------------

-- Demonstration accounts.
--
-- These are placeholder credentials created for this exercise. They are not
-- anyone's real sign-in details, and the addresses below are example.test
-- addresses rather than personal ones — nothing here identifies a person or
-- unlocks anything outside a local copy of this project.
--
-- Change them after importing if the site is ever served beyond localhost.

-- Administrator — full access. Username "admin", password "Admin@123456".
INSERT INTO users (role, username, email, password_hash, full_name, contact_number, address, is_active) VALUES
('admin', 'admin', 'admin@markme.test', '$2y$10$6CRkdb3pk5mkZIMPxpkrpe1TCDHlFAQ6ki8iHYXwz.W72w0p.RH/u', 'MarkMe Administrator', '09171234567', 'MarkMe HQ', 1);

-- Staff — restricted back-office access. Username "staff", password "Staff@123456".
-- Staff may view the catalog, inventory, customers and orders, restock products
-- and update order status — nothing else.
INSERT INTO users (role, username, email, password_hash, full_name, contact_number, address, is_active) VALUES
('staff', 'staff', 'staff@markme.test', '$2y$10$dQ46R33exOmQv2Te9ByOf.BtZwjJpgm3h19jnE7dQcyXoRc9.aihy', 'MarkMe Staff', '09171112233', 'MarkMe HQ', 1);

-- Customer accounts. Both use the password "Customer@123".
-- The first is the main demonstration account; the second gives the admin
-- Customers screen more than one row to show.
INSERT INTO users (role, username, email, password_hash, full_name, contact_number, address, city, province, postal_code, is_active) VALUES
('customer', 'customer', 'customer@markme.test', '$2y$10$aRa8anQYLeZevRgk6VkUxecbU8f7msGx5vqn8J3awqC0GsMBcvh1.', 'Demo Customer', '09179876543', '79 C. Raymundo Ave', 'Pasig', 'Metro Manila', '1600', 1),
('customer', 'juana', 'juana@markme.test', '$2y$10$aRa8anQYLeZevRgk6VkUxecbU8f7msGx5vqn8J3awqC0GsMBcvh1.', 'Juana Dela Cruz', '09171112222', '45 Mabini Ave', 'Manila', 'Metro Manila', '1000', 1);

INSERT INTO categories (name, description) VALUES
('Classic Bookmarks', 'Ready-made bookmark designs, no customization required'),
('Personalized Bookmarks', 'Fully customizable bookmarks with your own photo and text'),
('Gift Sets', 'Bundled bookmark sets for gifting');

INSERT INTO shapes (name, image_path) VALUES
('Rectangular', 'rectangular.svg'),
('Rounded', 'rounded.svg'),
('Heart', 'heart.svg'),
('Bookworm Tassel', 'tassel.svg');

INSERT INTO designs (name, image_path) VALUES
('Floral', 'floral.svg'),
('Minimalist Lines', 'minimalist.svg'),
('Starry Night', 'starry-night.svg'),
('Plain Kraft', 'plain-kraft.svg');

INSERT INTO products (category_id, name, description, price, stock_quantity, image_path, is_customizable) VALUES
(2, 'Photo Memory Bookmark', 'Upload your favorite photo and a short message on a durable laminated bookmark.', 99.00, 50, 'photo-memory.svg', 1),
(2, 'Name & Quote Bookmark', 'Add your name and a favorite quote to a custom-shaped bookmark.', 79.00, 40, 'name-quote.svg', 1),
(1, 'Classic Floral Bookmark', 'Pre-made floral design bookmark, ready to ship.', 49.00, 100, 'classic-floral.svg', 0),
(1, 'Minimalist Line Art Bookmark', 'Simple, elegant line-art bookmark for everyday reading.', 45.00, 0, 'minimalist-lineart.svg', 0),
(3, 'Reader\'s Gift Set (3-pack)', 'A set of three classic bookmarks, ready for gifting.', 129.00, 25, 'gift-set.svg', 0);

INSERT INTO settings (setting_key, setting_value) VALUES
('store_name',         'MarkMe'),
('store_tagline',      'Handmade, personalized bookmarks'),
('store_email',        'hello@markme.test'),
('store_phone',        '09171234567'),
('store_address',      'Manila, Philippines'),
('low_stock_default',  '5'),
('allow_registration', '1');
