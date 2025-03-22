CREATE DATABASE IF NOT EXISTS productsDb;
USE productsDb;

--item_id a productname jsou duplicitní a proto vynechané
CREATE TABLE IF NOT EXISTS products(
    id INT PRIMARY KEY NOT NULL,
    product VARCHAR(255),
    product_description TEXT,
    product_url VARCHAR(255),
    img_url TEXT,
    img_url_alternative TEXT,
    price_vat FLOAT,
    vat TINYINT,
    manufacturer VARCHAR(255),
    category_text VARCHAR(255),
    ean VARCHAR(255),
    delivery_date INT
);

CREATE TABLE IF NOT EXISTS params(
    id INT PRIMARY KEY NOT NULL,
    param_name VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS product_params(
    product_id INT,
    param_id INT,
    val VARCHAR(255),
    PRIMARY KEY(product_id, param_id),
    FOREIGN KEY(product_id) REFERENCES products(id),
    FOREIGN KEY(param_id) REFERENCES params(id)
);