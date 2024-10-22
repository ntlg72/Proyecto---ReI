CREATE DATABASE IF NOT EXISTS carritos_BD;

USE carritos_BD;

CREATE TABLE carritos (
  id_carrito INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  subtotal FLOAT,
  precioEnvio FLOAT,
  total FLOAT,
  username VARCHAR(50) 
);

CREATE TABLE items_carrito (
  id_item INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  carrito_id INT(11) NOT NULL,
  producto_id INT(11) NOT NULL,
  cantidad INT(11) NOT NULL,
  precio DECIMAL(10,2) NOT NULL
);


CREATE TABLE factura (
  id_factura INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  nombre VARCHAR(50) DEFAULT NULL,
  ciudad VARCHAR(50) DEFAULT NULL,
  direccion VARCHAR(100) DEFAULT NULL,
  documento_identidad VARCHAR(20) DEFAULT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  precio_envio DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE factura_items (
  id_item_factura INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  factura_id INT(11) DEFAULT NULL,
  product_id INT(11) DEFAULT NULL,
  price DECIMAL(10,2) DEFAULT NULL,
  quantity INT(11) DEFAULT NULL
);

