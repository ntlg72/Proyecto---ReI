const mysql = require('mysql2/promise');


const connection = mysql.createPool({
    host: 'productos_BD',
    user: 'root',
    password: 'root',
    port:'3306',
    database: 'productos_BD'
});

async function traerProductos() {
    const result = await connection.query('SELECT * FROM productos');
    return result[0];
}

async function traerProductosDesc() {
    const result = await connection.query('SELECT * FROM productos ORDER BY product_id DESC LIMIT 10');
    return result[0];
}


async function traerProducto(product_id) {
    const result = await connection.query('SELECT * FROM productos WHERE product_id= ?', product_id);
    return result[0];
}

async function traerProductosPorCategoria(product_category){
    const result = await connection.query('SELECT * FROM productos WHERE product_category= ?', product_category);
    return result[0];
}

async function traerCategorias() {
    const result = await connection.query('SELECT DISTINCT product_category FROM productos');
    return result[0]; // Retorna solo la parte de los resultados que contiene las filas
}

async function traerProductosPorNombre(product_name) {
    const [rows] = await connection.query('SELECT * FROM productos WHERE product_name = ?', [product_name]);
    return rows; // Devuelve las filas encontradas
}


async function actualizarProducto(product_id, product_stock) {
    const result = await connection.query('UPDATE productos SET product_stock = ? WHERE product_id = ?', [product_stock,product_id]);
    return result;
}


async function crearProducto(product_category,product_name,product_stock, unit_price_cop, product_url) {


    const result = await connection.query('INSERT INTO productos VALUES(null,?,?,?,?,?)', [product_category,product_name,product_stock, unit_price_cop, product_url]);
    return result;
}


async function borrarProducto(product_id) {
    const result = await connection.query('DELETE FROM productos WHERE product_id = ?', product_id);
    return result[0];
}


module.exports = {
    traerProductos, traerProducto, actualizarProducto, crearProducto, borrarProducto, traerProductosPorCategoria, traerCategorias, traerProductosDesc, traerProductosPorNombre
}
