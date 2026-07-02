CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    status VARCHAR(50),
    created_at DATETIME,
    PRIMARY KEY (id),
    INDEX idx_email (email)
);

CREATE TABLE orders (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    total DECIMAL(10,2),
    status VARCHAR(50),
    created_at DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE TABLE `delivery-orders` (
    id INT NOT NULL AUTO_INCREMENT,
    order_id INT NOT NULL,
    address VARCHAR(500),
    PRIMARY KEY (id),
    FOREIGN KEY (order_id) REFERENCES orders (id)
);
