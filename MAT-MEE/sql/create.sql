CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) DEFAULT NULL,
    google_id VARCHAR(100) DEFAULT NULL,
    otp_code VARCHAR(6) DEFAULT NULL,
    otp_expires DATETIME DEFAULT NULL,
    email_verified TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    image VARCHAR(255),
    description TEXT
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) UNIQUE NOT NULL,
    description LONGTEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    main_image VARCHAR(255),
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

ALTER TABLE products
  ADD COLUMN discount_price DECIMAL(10,2) DEFAULT NULL
    AFTER price;
 
-- discount_price: NULL = no discount, otherwise the sale price (must be < price)

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    size_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE(category_id, size_name)
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT,
    product_id INT,
    size_id INT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
    FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE SET NULL
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(255),
    total DECIMAL(12,2),
    shipping_charge DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    customer_name VARCHAR(100),
    customer_email VARCHAR(100),
    shipping_address LONGTEXT,
    phone VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    size_id INT NULL,
    quantity INT,
    price_at_purchase DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE SET NULL
);

CREATE TABLE metadata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT
);

CREATE TABLE IF NOT EXISTS `carousel_slides` (
    `id`               INT(11)       NOT NULL AUTO_INCREMENT,
    `title`            VARCHAR(255)  DEFAULT NULL,
    `subtitle`         VARCHAR(500)  DEFAULT NULL,
    `button_text`      VARCHAR(100)  DEFAULT NULL,
    `button_link`      VARCHAR(500)  DEFAULT NULL,
    `image_path`       VARCHAR(500)  NOT NULL,
    `title_size`       VARCHAR(10)   NOT NULL DEFAULT '3rem',
    `subtitle_size`    VARCHAR(10)   NOT NULL DEFAULT '1.1rem',
    `text_position`    ENUM('left','center','right') NOT NULL DEFAULT 'left',
    `text_valign`      ENUM('top','middle','bottom') NOT NULL DEFAULT 'middle',
    `overlay_opacity`  DECIMAL(3,2)  NOT NULL DEFAULT '0.45',
    `overlay_color`    VARCHAR(20)   NOT NULL DEFAULT '#000000',
    `text_color`       VARCHAR(20)   NOT NULL DEFAULT '#ffffff',
    `sort_order`       INT(5)        NOT NULL DEFAULT '0',
    `is_active`        TINYINT(1)    NOT NULL DEFAULT '1',
    `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_active_order` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample seed data

