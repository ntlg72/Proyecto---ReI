CREATE DATABASE IF NOT EXISTS productos_BD;

USE productos_BD;


CREATE TABLE IF NOT EXISTS productos (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_category VARCHAR(50),
    product_name VARCHAR(100),
    product_stock INT,
    unit_price_cop DECIMAL(10,2),
    product_url VARCHAR(500)
);