const express = require('express');
const router = express.Router();
const axios = require('axios');
const carritosModel = require('../models/carritosModel');


router.get('/carritos/:id_carrito', async (req, res) => {
    const id_carrito = req.params.id_carrito;
    var result;
    result = await carritosModel.traerCarrito(id_carrito);
    res.json(result[0]);
});


router.get('/carritos', async (req, res) => {
    var result;
    result = await carritosModel.traerCarritos();
    res.json(result);
});






// router.get('/carritovacio', async (req, res) => {
//     try {
//         // Obtener el ID del usuario desde el microservicio de productos
//         const userId = await axios.get('http://192.168.100.2:3001/usuarios/:username'); // Reemplazar con la URL correcta


//         // Verificar si existe un carrito para este usuario
//         const carritoExistente = await getCartByUser(userId.data.username);


//         if (!carritoExistente) {
//             // Crear un carrito vacío
//             const nuevoCarrito = await crearCarritoVacio(userId.data.id);
//             res.json(nuevoCarrito);
//         } else {
//             res.json({ message: 'Carrito ya existe' });
//         }
//     } catch (error) {
//         console.error(error);
//         res.status(500).json({ error: 'Error al crear el carrito' });
//     }
// });


const obtenerPrecioEnvio = (ciudad) => {
    const preciosEnvio = {
        'Bogotá': 5000,
        'Medellín': 6000,
        'Cali': 7000,
        'Cartagena': 8000,
    };
    return preciosEnvio[ciudad] || 10000; // Precio por defecto si la ciudad no está en el JSON
};



router.post('/create', async (req, res) => {
    const { username } = req.body;

    // Verificar si el username es null o undefined
    if (!username) {
        return res.status(400).json({ message: 'El username es obligatorio.' });
    }

    try {
        const cartId = await carritosModel.createCartIfNotExists(username);
        res.status(200).json({ message: 'Carrito creado', cartId });
    } catch (error) {
        console.error('Error al crear el carrito:', error.message);
        res.status(500).json({ message: 'Error interno del servidor' });
    }
});


router.post('/carrito', async (req, res) => {
    try {
        const carrito = req.body;

        // Validar datos del carrito aquí, si es necesario

        const result = await guardarCarrito(carrito);
        res.status(201).json({ message: 'Carrito guardado exitosamente', carritoId: result.insertId });
    } catch (error) {
        console.error('Error al guardar el carrito:', error);
        res.status(500).json({ message: 'Error interno del servidor' });
    }
});

router.post('/carrito/add', async (req, res) => {
    try {
        const { username, product, quantity } = req.body;

        if (!username || !product || !quantity) {
            return res.status(400).json({ message: 'Faltan datos necesarios' });
        }

        const result = await carritosModel.agregarACarrito(username, product, quantity);
        res.status(200).json(result);
    } catch (error) {
        console.error('Error al agregar el producto al carrito:', error);
        res.status(500).json({ message: 'Error interno del servidor' });
    }
});



router.post('/factura/crear', async (req, res) => {
    const { username, cartId } = req.body;
    try {
        // Llama a la función para crear la factura
        const response = await carritosModel.crearFactura(username, cartId);
        res.status(200).json(response);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

router.get('/facturas', async (req, res) => {
    var result;
    result = await carritosModel.traerFacturas();
    res.json(result);
});

// Controller para eliminar un producto del carrito
router.delete('/carrito/eliminar', async (req, res) => {
    const { username, productId } = req.body; // Obtener el username y productId del cuerpo de la solicitud

    try {
        // Llamar a la función que elimina el producto del carrito
        const response = await carritosModel.eliminarProductoCarrito(username, productId);
        
        // Responder con éxito y los ítems restantes en el carrito
        res.status(200).json({
            message: response.message,
            items: response.items,
        });
    } catch (error) {
        // Enviar un error en caso de que algo falle
        res.status(500).json({ error: error.message });
    }
});



// Ruta para modificar la cantidad de un producto en el carrito
router.post('/carrito/actualizar', async (req, res) => {
    const { username, product_id, quantity } = req.body;

    // Validación de entrada
    if (!username || !product_id || !quantity || quantity <= 0) {
        return res.status(400).json({ message: 'Parámetros inválidos' });
    }

    try {
        const response = await carritosModel.modificarCantidadCarrito(username, product_id, quantity);
        res.status(200).json(response);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});


router.delete('/carrito/vaciar', async (req, res) => {
    const { cartId } = req.body;
    try {
        const response = await carritosModel.vaciarCarrito(cartId);
        res.status(200).json(response);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Ruta para obtener los ítems del carrito por username
router.get('/carrito/items/:username', async (req, res) => {
    const { username } = req.params;

    try {
        const response = await carritosModel.obtenerItemsCarritoPorUsuario(username);
        res.status(200).json(response);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});


module.exports = router;
