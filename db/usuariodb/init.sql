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

USE usuarios_BD;


INSERT INTO usuarios (username, email, nombre, password, customer_city, direccion, documento_de_identidad) VALUES ('admin', 'admin@example.com', 'Hugh Womble', '1234', 'Cartagena', 'Cr. 167 Bis Q # 4-96 Sur, El Tarra, 706881', '615957315');
INSERT INTO usuarios (username, email, nombre, password, customer_city, direccion, documento_de_identidad) VALUES ('martin.ling7414', 'luis34@example.net', 'Martin Ling', '9_44+MN9jk', 'Bogotá', 'Av. San Francisco # 92Y-6 Sur, Guayabetal, 542375', '6238537797');
