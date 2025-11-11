
SET FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS carriemart DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
USE carriemart;

CREATE TABLE accounts (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  address VARCHAR(255),
  phone_number VARCHAR(20),
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  profile_photo_url VARCHAR(255),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  INDEX idx_accounts_email (email),
  INDEX idx_accounts_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE positions (
  position_id INT AUTO_INCREMENT PRIMARY KEY,
  position_name VARCHAR(100) NOT NULL,
  monthly_rate DECIMAL(12,2) DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_positions_name (position_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE suppliers (
  supplier_id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_name VARCHAR(100) NOT NULL,
  contact_person VARCHAR(100),
  contact_number VARCHAR(30),
  email VARCHAR(100),
  address VARCHAR(255),
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_suppliers_name (supplier_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL UNIQUE,
  url_name VARCHAR(120) NOT NULL UNIQUE, -- slug, name for urls
  description TEXT,
  photo_url VARCHAR(500),
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_categories_name (category_name),
  INDEX idx_categories_slug (url_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE brands (
  brand_id INT AUTO_INCREMENT PRIMARY KEY,
  brand_name VARCHAR(100) NOT NULL UNIQUE,
  logo_url VARCHAR(500),
  website VARCHAR(255),
  description TEXT,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_brands_name (brand_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  product_id INT AUTO_INCREMENT PRIMARY KEY,
  product_name VARCHAR(150) NOT NULL,
  brand_id INT DEFAULT NULL,
  model VARCHAR(100),
  category_id INT DEFAULT NULL,
  retail_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  cost_price DECIMAL(12,2) NOT NULL,
  supplier_id INT DEFAULT NULL,
  description TEXT,
  specifications TEXT,
  product_condition ENUM('new','used','refurbished') DEFAULT 'new',
  warranty_months INT DEFAULT 12,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  stock_level INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_products_name (product_name),
  INDEX idx_products_brand (brand_id),
  INDEX idx_products_category (category_id),
  INDEX idx_products_supplier (supplier_id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (brand_id) REFERENCES brands(brand_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_photos (
  product_photo_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  photo_url VARCHAR(500) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pp_product (product_id),
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employees (
  emp_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  birth_date DATE DEFAULT NULL,
  gender ENUM('male','female','other') DEFAULT NULL,
  employment_status ENUM('active','inactive','terminated','on_leave') DEFAULT 'active',
  hire_date DATE DEFAULT NULL,
  current_position_id INT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_employees_user (user_id),
  INDEX idx_employees_position (current_position_id),
  FOREIGN KEY (user_id) REFERENCES accounts(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (current_position_id) REFERENCES positions(position_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE salaries (
  salary_id INT AUTO_INCREMENT PRIMARY KEY,
  emp_id INT NOT NULL,
  pay_date DATE NOT NULL,
  rate_used DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('pending','paid','cancelled') DEFAULT 'pending',
  from_date DATE DEFAULT NULL,
  to_date DATE DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_salaries_emp (emp_id),
  FOREIGN KEY (emp_id) REFERENCES employees(emp_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vouchers (
  voucher_id INT AUTO_INCREMENT PRIMARY KEY,
  voucher_code VARCHAR(20) NOT NULL UNIQUE,
  percent_sale INT DEFAULT NULL,
  min_purchase_amount DECIMAL(12,2) DEFAULT 0.00,
  max_discount_amount DECIMAL(12,2) DEFAULT NULL,
  max_uses INT DEFAULT NULL,
  times_used INT DEFAULT 0,
  from_date DATE DEFAULT NULL,
  to_date DATE DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_vouchers_code (voucher_code),
  INDEX idx_vouchers_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart (
  cart_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cart_user (user_id),
  FOREIGN KEY (user_id) REFERENCES accounts(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cart_product (
  cart_product_id INT AUTO_INCREMENT PRIMARY KEY,
  cart_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cart_product_cart (cart_id),
  INDEX idx_cart_product_product (product_id),
  UNIQUE KEY ux_cart_product_cart_product (cart_id, product_id),
  FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  voucher_code VARCHAR(20) DEFAULT NULL,
  date_ordered TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
  order_status ENUM('pending','processing','shipped','completed','cancelled') DEFAULT 'pending',
  payment_option VARCHAR(100) DEFAULT NULL,
  delivery_recipient VARCHAR(100) DEFAULT NULL,
  delivery_address VARCHAR(255) DEFAULT NULL,
  delivery_phone VARCHAR(30) DEFAULT NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  discount_amount DECIMAL(12,2) DEFAULT 0.00,
  delivery_fee DECIMAL(12,2) DEFAULT 0.00,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  completed_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orders_user (user_id),
  INDEX idx_orders_voucher (voucher_code),
  FOREIGN KEY (user_id) REFERENCES accounts(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (voucher_code) REFERENCES vouchers(voucher_code) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_order (
  product_order_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_product_order_order (order_id),
  INDEX idx_product_order_product (product_id),
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE returns (
  return_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT DEFAULT NULL,
  return_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  return_status ENUM('requested','approved','rejected','processed') DEFAULT 'requested',
  refund_amount DECIMAL(12,2) DEFAULT 0.00,
  processed_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_returns_order (order_id),
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_return (
  product_return_id INT AUTO_INCREMENT PRIMARY KEY,
  return_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity_returned INT NOT NULL DEFAULT 1,
  reason VARCHAR(255) DEFAULT NULL,
  cond ENUM('new','opened','damaged','other') DEFAULT 'other',
  INDEX idx_product_return_return (return_id),
  INDEX idx_product_return_product (product_id),
  FOREIGN KEY (return_id) REFERENCES returns(return_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE expenses (
  exp_id INT AUTO_INCREMENT PRIMARY KEY,
  expense_type ENUM('inventory_purchase','shipping','maintenance','rent','utilities','other') DEFAULT 'other',
  description VARCHAR(255),
  amount DECIMAL(12,2) DEFAULT 0.00,
  status ENUM('pending','paid') DEFAULT 'pending',
  due_date DATE DEFAULT NULL,
  paid_date DATE DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_expenses_status (status),
  INDEX idx_expenses_type (expense_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_review (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT DEFAULT NULL,
  order_id INT DEFAULT NULL,
  rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  review_title VARCHAR(150),
  review_text TEXT,
  is_verified_purchase TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_product_review_product (product_id),
  INDEX idx_product_review_user (user_id),
  INDEX idx_product_review_order (order_id),
  INDEX idx_product_review_rating (rating),
  UNIQUE KEY ux_product_review_user_product (user_id, product_id),
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (user_id) REFERENCES accounts(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;