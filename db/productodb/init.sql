CREATE DATABASE productos_BD;

USE productos_BD;


CREATE TABLE productos (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_category VARCHAR(50),
    product_name VARCHAR(100),
    product_stock INT,
    unit_price_cop DECIMAL(10,2)
);

USE productos_BD;

INSERT INTO productos VALUES ('9993', 'Tecnología', 'Smartphone', '48', '209421', 'https://img.freepik.com/vector-gratis/pantalla-realista-smartphone-diferentes-aplicaciones_52683-30241.jpg');
INSERT INTO productos VALUES ('9994', 'Alimentos', 'Arroz Integral', '80', '3444405', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRbJLBI3o26esIT8D4n-6uYDCXRxg-RJEk-gw&s');
INSERT INTO productos VALUES ('9995', 'Juguetes', 'Lego Star Wars', '14', '1138504', 'https://media.admagazine.com/photos/61f9616c9b19d943aa117c2a/3:2/w_1278,h_852,c_limit/GettyImages-521952820.jpg');
INSERT INTO productos VALUES ('9996', 'Juguetes', 'Muñeca Barbie', '69', '2307502', 'https://arisma.com.co/wp-content/uploads/2021/08/products-WhatsApp-Image-2021-08-09-at-1.12.01-PM-(2).jpeg');
INSERT INTO productos VALUES ('9997', 'Ropa', 'Camisa de Algodón', '25', '3307641', 'https://hmcolombia.vtexassets.com/arquivos/ids/3941478/Camisa-Slim-Fit---Negro---H-M-CO.jpg?v=638606870434200000');
INSERT INTO productos VALUES ('9998', 'Ropa', 'Jeans', '74', '3163928', 'https://offcorss.vtexassets.com/arquivos/ids/804333-800-auto?v=638264226764870000&width=800&height=auto&aspect=true');
