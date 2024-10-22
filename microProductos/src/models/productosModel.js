const mysql = require('mysql2/promise');


const connection = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '',
    port:'3306',
    database: 'productos_BD'
});



async function traerProductos() {
    const result = await connection.query('SELECT * FROM productos');
    return result[0];
}


async function traerProducto(product_id) {
    const result = await connection.query('SELECT * FROM productos WHERE product_id= ?', product_id);
    return result[0];
}


async function actualizarProducto(product_id, product_stock) {
    const result = await connection.query('UPDATE productos SET product_stock = ? WHERE product_id = ?', [product_stock,product_id]);
    return result;
}


async function crearProducto(product_category,product_name,product_stock, unit_price_cop) {


    const result = await connection.query('INSERT INTO productos VALUES(null,?,?,?,?)', [product_category,product_name,product_stock, unit_price_cop]);
    return result;
}


async function borrarProducto(product_id) {
    const result = await connection.query('DELETE FROM productos WHERE product_id = ?', product_id);
    return result[0];
}


module.exports = {
    traerProductos, traerProducto, actualizarProducto, crearProducto, borrarProducto
}