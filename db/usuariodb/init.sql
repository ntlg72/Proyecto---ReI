CREATE DATABASE IF NOT EXISTS usuarios_BD;

USE usuarios_BD;

CREATE TABLE IF NOT EXISTS usuarios (

    username VARCHAR(50) PRIMARY KEY,
    email VARCHAR(100),
    nombre VARCHAR(50),
    password VARCHAR(50),
    customer_city VARCHAR(50),
    direccion VARCHAR(100),
    documento_de_identidad VARCHAR(20)
);