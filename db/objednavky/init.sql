CREATE DATABASE IF NOT EXISTS ordersDb;
USE ordersDb;

CREATE TABLE IF NOT EXISTS orders(
    id INT PRIMARY KEY NOT NULL,
    order_date DATE,
    customer_first_name VARCHAR(255),
    customer_last_name VARCHAR(255),
    customer_address VARCHAR(255),
    customer_email VARCHAR(255),
    customer_phone VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS order_items(
    order_id INT,
    product_id INT,
    quantity INT,
    PRIMARY KEY(order_id, product_id),
    FOREIGN KEY(order_id) REFERENCES orders(id),
    FOREIGN KEY(product_id) REFERENCES products(id)
);