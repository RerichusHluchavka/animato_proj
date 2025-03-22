CREATE DATABASE IF NOT EXISTS ordersDb;
USE ordersDb;

CREATE TABLE IF NOT EXISTS partners(
    id INT AUTO_INCREMENT PRIMARY KEY,
    param_company VARCHAR(255),
    partner_name VARCHAR(255),
    partner_city VARCHAR(255),
    partner_street VARCHAR(255),
    partner_zip VARCHAR(255),
    partner_ico VARCHAR(255),
    partner_dic VARCHAR(255),
    partner_fax VARCHAR(255),
    partner_phone VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS payment_type(
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_type VARCHAR(255),
    payment_ids VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS orders(
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE,
    delivery_date DATE,
    orderType VARCHAR(255),
    order_text TEXT,
    partner_id INT,
    payment_type_id INT,
    FOREIGN KEY(payment_type_id) REFERENCES payment_type(id),
    FOREIGN KEY(partner_id) REFERENCES partners(id)
);

CREATE TABLE IF NOT EXISTS order_items(
    order_id INT,
    product_id INT,
    quantity INT,
    PRIMARY KEY(order_id, product_id),
    FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE
);

INSERT INTO payment_type (payment_type, payment_ids) VALUES ('draft', 'příkazem');
INSERT INTO partners (param_company, partner_name, partner_city, partner_street, partner_zip, partner_ico, partner_dic, partner_phone, partner_fax) 
VALUES ('ACO nábytkové prvky s.r.o.', 'Michal Dolejší', 'Opava 1', 'Jana Nerudy 6', '746 01', '55967724', 'CZ55967724', '553 387 734', '553 466 651');