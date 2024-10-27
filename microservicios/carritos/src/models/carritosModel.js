const mysql = require('mysql2/promise');
const axios = require('axios');


const connection = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '',
    port: '3306',
    database: 'carritos_BD'
});

// async function crearCarritoVacio(userID) {
//     const result = await connection.query('INSERT INTO carritos VALUES (null, ?, [], ?, ?, ?)', [userID]);
//     return result;
// }

async function createCartIfNotExists(username) {
    console.log('Valor de username:', username); // Verificar si el username es correcto

    // Permitir que el usuario "admin" se loguee pero no crear un carrito
    if (username === 'admin') {
        return null; // No se crea un carrito para el usuario "admin"
    }

    // Verificar la existencia del usuario a través de la API externa
    try {
        await axios.get(`http://usuarios:3001/usuarios/${username}`);
    } catch (error) {
        throw new Error('El usuario no existe o no se pudo verificar la existencia del usuario.');
    }

    // Verificar si el carrito ya existe para el usuario
    const [existingCart] = await connection.query('SELECT id_carrito FROM carritos WHERE username = ?', [username]);

    if (existingCart.length === 0) {
        // Crear un nuevo carrito vacío
        try {
            // Agregar el console.log aquí para verificar el valor de username
            console.log('Inserting into carritos with username:', username);
            
            // Asegúrate de que el campo usuario_id sea del tipo correcto en la base de datos
            const [result] = await connection.query('INSERT INTO carritos (subtotal, precioEnvio, total, username) VALUES (0, 0, 0, ?)', [username]);
            return result.insertId; // Retornar el ID del carrito creado
        } catch (error) {
            throw new Error('Error al crear el carrito: ' + error.message);
        }
    }

    return existingCart[0].id_carrito; // Retornar el ID del carrito existente
}



async function traerCarrito(id_carrito) {
    const result = await connection.query('SELECT * FROM carritos WHERE id_carrito = ?', id_carrito);
    return result[0];
}




async function traerCarritos() {
    const result = await connection.query('SELECT * FROM carritos');
    return result[0];
}

// async function agregarACarrito(username, product, quantity) {
//     // Obtener el precio del producto desde la API
//     let productPrice;
//     try {
//         const productoResponse = await axios.get(`http://productos:3002/productos/${product.id}`);
//         productPrice = productoResponse.data.precio;
//     } catch (error) {
//         throw new Error('No se pudo obtener el precio del producto.');
//     }

//     // Verificar si el carrito del usuario ya existe
//     const existingCart = await connection.query('SELECT * FROM carritos WHERE usuario_id = ?', [username]);

//     let carritoId;
//     if (existingCart.length) {
//         // Obtener el ID del carrito existente
//         carritoId = existingCart[0].id;
//     } else {
//         // Crear un nuevo carrito si no existe
//         const result = await connection.query('INSERT INTO carritos (subtotal, precioEnvio, total, usuario_id) VALUES (0, 0, 0, ?)', [username]);
//         carritoId = result.insertId;
//     }

//     // Verificar si el producto ya está en el carrito
//     const existingProduct = await connection.query('SELECT * FROM items_carrito WHERE carrito_id = ? AND producto_id = ?', [carritoId, product.id]);

//     if (existingProduct.length) {
//         // Si existe, actualizar la cantidad
//         await connection.query('UPDATE items_carrito SET cantidad = cantidad + ? WHERE carrito_id = ? AND producto_id = ?', [quantity, carritoId, product.id]);
//     } else {
//         // Si no existe, insertar el nuevo producto en el carrito
//         await connection.query('INSERT INTO items_carrito (carrito_id, producto_id, precio, cantidad) VALUES (?, ?, ?, ?)', [carritoId, product.id, productPrice, quantity]);
//     }

//     // Opcionalmente, puedes retornar un mensaje de éxito o el carrito actualizado
//     return { message: 'Producto añadido al carrito' };
//}

async function guardarCarrito(carrito) {
    const { subtotal, precioEnvio, total, usuario_id, items } = carrito;

    // Insertar el carrito en la base de datos
    const result = await connection.query('INSERT INTO carritos (subtotal, precioEnvio, total, usuario_id) VALUES (?, ?, ?, ?)', [subtotal, precioEnvio, total, usuario_id]);

    // Obtener el ID del carrito recién creado
    const carritoId = result.insertId;

    // Procesar cada item en el carrito
    for (const item of items) {
        const { producto_id, cantidad } = item;

        // Obtener información del producto desde la API
        const productoResponse = await axios.get(`http://productos:3002/productos/${producto_id}`);
        const producto = productoResponse.data;

        // Obtener el precio del producto desde la API
        const precio = producto.precio;

        // Insertar el item en la base de datos
        await connection.query('INSERT INTO items_carrito (carrito_id, producto_id, precio, cantidad) VALUES (?, ?, ?, ?)', 
                               [carritoId, producto_id, precio, cantidad]);
    }

    return result;
}

async function obtenerCiudadUsuario(username) {
    // Obtener la información del usuario desde la API o base de datos externa
    try {
        const usuarioResponse = await axios.get(`http://usuarios:3001/usuarios/${username}`);
        const usuario = usuarioResponse.data;

        if (!usuario || !usuario.customer_city) {
            throw new Error('Usuario o ciudad no encontrada.');
        }

        return usuario.customer_city;
    } catch (error) {
        console.error('Error al obtener la ciudad del usuario:', error.message);
        throw new Error('No se pudo obtener la ciudad del usuario.');
    }
}



async function actualizarCarrito(carritoId) {
    try {
        // Obtener todos los productos restantes en el carrito
        const [items] = await connection.query('SELECT * FROM items_carrito WHERE carrito_id = ?', [carritoId]);

        // Si no quedan productos, el subtotal debe ser 0
        if (items.length === 0) {
            await connection.query('UPDATE carritos SET subtotal = 0, precioEnvio = 0, total = 0 WHERE id_carrito = ?', [carritoId]);
            console.log('Carrito vacío, subtotal y total establecidos a 0.');
            return;
        }

        // Calcular el subtotal
        const subtotal = items.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);

        // Valor del envío
        const valorEnvio = 10000;

        // Calcular el total
        const total = subtotal + valorEnvio;

        // Actualizar el carrito en la base de datos
        await connection.query(
            'UPDATE carritos SET subtotal = ?, precioEnvio = ?, total = ? WHERE id_carrito = ?',
            [subtotal, valorEnvio, total, carritoId]
        );

        console.log(`Carrito ${carritoId} actualizado: Subtotal: ${subtotal}, Total: ${total}`);
    } catch (error) {
        console.error('Error al actualizar el carrito:', error.message);
        throw new Error('No se pudo actualizar el carrito.');
    }
}



async function agregarACarrito(username, product, quantity) {
    try {
        // Obtener el precio del producto desde la API
        const productoResponse = await axios.get(`http://productos:3002/productos/${product.id}`);
        const productPrice = productoResponse.data.unit_price_cop;

        if (!productPrice) {
            throw new Error('El precio del producto es nulo o indefinido.');
        }

        // Verificar si el carrito del usuario ya existe
        const [existingCart] = await connection.query('SELECT id_carrito FROM carritos WHERE username = ?', [username]);

        let carritoId;
        if (existingCart.length) {
            // Obtener el ID del carrito existente
            carritoId = existingCart[0].id_carrito;
        } else {
            // Crear un nuevo carrito si no existe
            const [result] = await connection.query('INSERT INTO carritos (subtotal, precioEnvio, total, username) VALUES (?, ?, ?, ?)', [0, 0, 0, username]);
            carritoId = result.insertId;
        }

        // Verificar si el producto ya está en el carrito
        const [existingProduct] = await connection.query('SELECT * FROM items_carrito WHERE carrito_id = ? AND producto_id = ?', [carritoId, product.id]);

        if (existingProduct.length) {
            // Si existe, actualizar la cantidad
            await connection.query('UPDATE items_carrito SET cantidad = cantidad + ? WHERE carrito_id = ? AND producto_id = ?', [quantity, carritoId, product.id]);
        } else {
            // Si no existe, insertar el nuevo producto en el carrito
            await connection.query('INSERT INTO items_carrito (carrito_id, producto_id, precio, cantidad) VALUES (?, ?, ?, ?)', [carritoId, product.id, productPrice, quantity]);
        }

        // Actualizar el carrito con el nuevo subtotal, valor de envío y total
        await actualizarCarrito(carritoId, username);

        return { message: 'Producto añadido al carrito' };
    } catch (error) {
        console.error('Error al agregar el producto al carrito:', error.message);
        throw new Error('No se pudo agregar el producto al carrito.');
    }
}


async function crearFactura(username, cartId) {
    // Obtener los productos del carrito desde la base de datos
    const [cartItems] = await connection.query('SELECT * FROM items_carrito WHERE carrito_id = ?', [cartId]);

    // Obtener la información del usuario desde la API
    let user;
    try {
        const userResponse = await axios.get(`http://usuarios:3001/usuarios/${username}`);
        user = userResponse.data;
    } catch (error) {
        throw new Error('No se pudo obtener la información del usuario.');
    }

    const precioEnvio = 10000;

    // Calcular el subtotal
    const subtotal = cartItems.reduce((acc, item) => acc + item.precio * item.cantidad, 0);
    const total = subtotal + precioEnvio;

    // Crear la factura en la base de datos
    const [facturaResult] = await connection.query('INSERT INTO factura (user_id, email, nombre, ciudad, direccion, documento_identidad, subtotal, precio_envio, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', 
        [username, user.email, user.nombre, user.customer_city, user.direccion, user.documento_de_identidad, subtotal, precioEnvio, total]);

    // Obtener el ID de la factura creada
    const facturaId = facturaResult.insertId;

    // Insertar los productos en la tabla de factura_items y actualizar el inventario
    for (const item of cartItems) {
        await connection.query('INSERT INTO factura_items (factura_id, product_id, price, quantity) VALUES (?, ?, ?, ?)', 
            [facturaId, item.producto_id, item.precio, item.cantidad]);

        // Obtener el stock actual del producto
        let product;
        try {
            const productResponse = await axios.get(`http://productos:3002/productos/${item.producto_id}`);
            product = productResponse.data;
        } catch (error) {
            throw new Error(`Error al obtener el producto ${item.producto_id}: ${error.message}`);
        }

        // Calcular el nuevo stock restando la cantidad comprada
        const newStock = product.product_stock - item.cantidad;

        // Actualizar el inventario usando la ruta proporcionada
        try {
            await axios.put(`http://productos:3002/productos/${item.producto_id}`, {
                product_stock: newStock // Enviar el nuevo stock calculado
            });
        } catch (error) {
            throw new Error(`Error al actualizar el inventario para el producto ${item.producto_id}: ${error.message}`);
        }
    }

    // Vaciar el carrito después de crear la factura
    try {
        await vaciarCarrito(cartId);
    } catch (error) {
        throw new Error('No se pudo vaciar el carrito después de crear la factura.');
    }

    // Obtener los detalles de la factura recién creada
    const [facturaDetails] = await connection.query('SELECT * FROM factura WHERE id_factura = ?', [facturaId]);

    // Verificar el resultado de la consulta
    if (facturaDetails.length === 0) {
        throw new Error('No se encontraron detalles para la factura creada.');
    }

    // Devolver los detalles de la factura como JSON
    return {
        id_factura: facturaDetails[0].id_factura,
        user_id: facturaDetails[0].user_id,
        email: facturaDetails[0].email,
        nombre: facturaDetails[0].nombre,
        ciudad: facturaDetails[0].ciudad,
        direccion: facturaDetails[0].direccion,
        documento_identidad: facturaDetails[0].documento_identidad,
        subtotal: facturaDetails[0].subtotal,
        precio_envio: facturaDetails[0].precio_envio,
        total: facturaDetails[0].total,
        fecha: facturaDetails[0].fecha
    };
}




async function eliminarProductoCarrito(username, productId) {
    try {
        // Obtener el carrito del usuario
        const [existingCart] = await connection.query('SELECT id_carrito FROM carritos WHERE username = ?', [username]);

        if (!existingCart || existingCart.length === 0) {
            throw new Error('El carrito del usuario no existe.');
        }

        const carritoId = existingCart[0].id_carrito;

        // Verificar si el producto ya está en el carrito
        const [existingItem] = await connection.query('SELECT * FROM items_carrito WHERE carrito_id = ? AND producto_id = ?', [carritoId, productId]);

        if (!existingItem || existingItem.length === 0) {
            console.log('Carrito ID:', carritoId);
            console.log('Product ID:', productId);
            console.log('Existing Item:', existingItem);
            throw new Error('El producto no está en el carrito.');
        }

        // Eliminar el producto del carrito
        await connection.query('DELETE FROM items_carrito WHERE carrito_id = ? AND producto_id = ?', [carritoId, productId]);

        // Obtener el carrito actualizado (opcional)
        const [updatedCartItems] = await connection.query('SELECT * FROM items_carrito WHERE carrito_id = ?', [carritoId]);

        // Actualizar el carrito con el nuevo subtotal, valor de envío y total
        await actualizarCarrito(carritoId, username);
        
        return {
            message: 'Producto eliminado correctamente',
            items: updatedCartItems
        };
    } catch (error) {
        console.error('Error al eliminar el producto del carrito:', error.message);
        throw new Error('No se pudo eliminar el producto del carrito.');
    }
}

async function traerFacturas() {
    const result = await connection.query('SELECT * FROM factura');
    return result[0];
}

async function traerFacturasCiudad(ciudad){
    const result = await connection.query('SELECT * FROM factura WHERE ciudad= ?', ciudad);
    return result[0];
}

async function traerCiudad() {
    const result = await connection.query('SELECT DISTINCT ciudad FROM factura ');
    return result[0];
}


async function vaciarCarrito(cartId) {
    try {
        // Eliminar los productos del carrito
        await connection.query('DELETE FROM items_carrito WHERE carrito_id = ?', [cartId]);

        // Obtener el valor de envío (esto puede ser una constante o un valor calculado)
        const valorEnvio = 10000; // Ajusta este valor según tus necesidades

        // Actualizar el carrito con subtotal 0 y total igual al precio de envío
        await connection.query('UPDATE carritos SET subtotal = ?, precioEnvio = ?, total = ? WHERE id_carrito = ?', [0, valorEnvio, valorEnvio, cartId]);

        return { message: 'Carrito vaciado correctamente' };
    } catch (error) {
        console.error('Error al vaciar el carrito:', error.message);
        throw new Error('No se pudo vaciar el carrito.');
    }
}


async function obtenerItemsCarritoPorUsuario(username) {
    try {
        console.log('Buscando carrito para el usuario:', username); // Log para verificar el username
        // Obtener el ID del carrito asociado al usuario
        const [userCart] = await connection.query('SELECT id_carrito FROM carritos WHERE username = ?', [username]);

        if (userCart.length === 0) {
            return { message: 'El usuario no tiene un carrito activo', items: [] };
        }

        const cartId = userCart[0].id_carrito;

        // Obtener los productos del carrito
        const [cartItems] = await connection.query('SELECT * FROM items_carrito WHERE carrito_id = ?', [cartId]);

        if (cartItems.length === 0) {
            return { message: 'El carrito está vacío', items: [] };
        }

        return { message: 'Items del carrito obtenidos correctamente', items: cartItems };
    } catch (error) {
        throw new Error('Error al obtener los items del carrito: ' + error.message);
    }
}

async function modificarCantidadCarrito(username, productId, cantidad) {
    // Obtener el carrito del usuario
    const [existingCart] = await connection.query('SELECT * FROM carritos WHERE username = ?', [username]);

    if (!existingCart || existingCart.length === 0) {
        throw new Error('El carrito del usuario no existe.');
    }

    const carritoId = existingCart[0].id_carrito;

    // Verificar si el producto ya está en el carrito
    const [existingItem] = await connection.query('SELECT * FROM items_carrito WHERE carrito_id = ? AND producto_id = ?', [carritoId, productId]);

    if (!existingItem || existingItem.length === 0) {
        throw new Error('El producto no está en el carrito.');
    }

    // Actualizar la cantidad del producto en el carrito
    await connection.query('UPDATE items_carrito SET cantidad = ? WHERE carrito_id = ? AND producto_id = ?', [cantidad, carritoId, productId]);

    // Llamar a la función actualizarCarrito para recalcular subtotal y total
    await actualizarCarrito(carritoId);

    // Obtener el carrito actualizado (opcional)
    const [updatedCartItems] = await connection.query('SELECT * FROM items_carrito WHERE carrito_id = ?', [carritoId]);

    return {
        message: 'Cantidad actualizada correctamente',
        items: updatedCartItems
    };
}



module.exports = {
    crearFactura,
    traerCarrito,
    traerCarritos,
    agregarACarrito,
    guardarCarrito,
    createCartIfNotExists,
    actualizarCarrito,
    eliminarProductoCarrito,
    traerFacturas,
    vaciarCarrito,
    obtenerItemsCarritoPorUsuario,
    modificarCantidadCarrito,
    traerFacturasCiudad,
    traerCiudad

    
};
