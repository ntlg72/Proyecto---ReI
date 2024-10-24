CREATE DATABASE productos_BD;

USE productos_BD;


CREATE TABLE productos (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_category VARCHAR(50),
    product_name VARCHAR(100),
    product_stock INT,
    unit_price_cop DECIMAL(10,2)
);
