CREATE DATABASE usuarios_BD;

USE usuarios_BD;

CREATE TABLE usuarios (

    username VARCHAR(50) PRIMARY KEY,
    email VARCHAR(100),
    nombre VARCHAR(50),
    password VARCHAR(50),
    customer_city VARCHAR(50),
    direccion VARCHAR(100),
    documento_de_identidad VARCHAR(20)
);


CREATE DATABASE productos_BD;

USE productos_BD;


CREATE TABLE productos (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_category VARCHAR(50),
    product_name VARCHAR(100),
    product_stock INT,
    unit_price_cop DECIMAL(10,2),
    product_url VARCHAR(200)
);