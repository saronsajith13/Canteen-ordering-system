CREATE DATABASE IF NOT EXISTS canteen_db;
USE canteen_db;

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin','customer','cashier') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL
);

CREATE TABLE food_items (
    food_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    food_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    status VARCHAR(20) DEFAULT 'available',
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    order_status VARCHAR(50) DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status VARCHAR(50) DEFAULT 'unpaid',
    card_holder_name VARCHAR(255) DEFAULT '',
    card_last_four VARCHAR(4) DEFAULT '',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    food_id INT,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES food_items(food_id) ON DELETE SET NULL
);

CREATE TABLE feedback (
    feedback_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    food_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (food_id) REFERENCES food_items(food_id) ON DELETE SET NULL
);

CREATE TABLE shop_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shop_name VARCHAR(255) NOT NULL DEFAULT '',
    shop_id VARCHAR(100) NOT NULL DEFAULT '',
    bank_name VARCHAR(255) NOT NULL DEFAULT '',
    account_holder VARCHAR(255) NOT NULL DEFAULT '',
    account_number VARCHAR(100) NOT NULL DEFAULT '',
    ifsc_code VARCHAR(50) NOT NULL DEFAULT '',
    upi_id VARCHAR(100) NOT NULL DEFAULT '',
    qr_code_image VARCHAR(255) NOT NULL DEFAULT ''
);

INSERT INTO shop_settings (shop_name, shop_id, bank_name, account_holder, account_number, ifsc_code, upi_id) VALUES
('Canteen', 'CANTEEN001', '', '', '', '', '');

INSERT INTO users (full_name, email, password, role) VALUES
('Admin', 'admin@canteen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Cashier', 'cashier@canteen.com', '$2y$10$2c3w5gOWlE1bMS7AgM2s2.aa.seTQ272wJYmYX8WTSs6rca.14pIK', 'cashier');

INSERT INTO categories (category_name) VALUES
('Breakfast'),
('Lunch'),
('Snacks'),
('Beverages'),
('Desserts');

INSERT INTO food_items (category_id, food_name, description, price, image, status) VALUES
(1, 'Idli', 'Soft rice idli with sambar and chutney', 66.00, 'idli.svg', 'available'),
(1, 'Dosa', 'Crispy masala dosa with chutney', 110.00, 'dosa.svg', 'available'),
(2, 'Rice Meal', 'Full meal with rice, dal, sabzi, papad', 176.00, 'rice_meal.svg', 'available'),
(2, 'Biryani', 'Chicken biryani with raita', 264.00, 'biryani.svg', 'available'),
(3, 'Samosa', 'Crispy fried samosa with chutney', 44.00, 'samosa.svg', 'available'),
(3, 'French Fries', 'Crispy french fries with ketchup', 132.00, 'fries.svg', 'available'),
(4, 'Tea', 'Hot masala tea', 33.00, 'tea.svg', 'available'),
(4, 'Coffee', 'Filter coffee', 55.00, 'coffee.svg', 'available'),
(5, 'Gulab Jamun', 'Soft gulab jamun (2 pieces)', 88.00, 'gulab_jamun.svg', 'available'),
(5, 'Ice Cream', 'Vanilla ice cream scoop', 77.00, 'ice_cream.svg', 'available');
