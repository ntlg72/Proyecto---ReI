const { Router } = require('express');
const router = Router();
const axios = require('axios');
const usuariosModel = require('../models/usuariosModel');


router.post('/usuarios/crear', async (req, res) => {
    const username = req.body.username;
    const email = req.body.email;
    const nombre = req.body.nombre;
    const password = req.body.password;
    const customer_city = req.body.customer_city;
    const direccion = req.body.direccion;
    const documento_de_identidad = req.body.documento_de_identidad;
    
    try {
        // Crear el usuario
        var result = await usuariosModel.crearUsuario(username, email, nombre, password, customer_city, direccion, documento_de_identidad);
        
        // Enviar respuesta en formato JSON
        res.json({
            success: true,
            message: "Usuario creado exitosamente."
        });
    } catch (error) {
        // Manejar errores y enviar respuesta adecuada
        res.json({
            success: false,
            message: "Error al crear el usuario: " + error.message
        });
    }
});


router.get('/usuarios/:username', async (req, res) => {
    const username = req.params.username;
    var result;
    result = await usuariosModel.traerUsuario(username) ;
    res.json(result[0]);
});

router.get('/usuarios/:username/:password', async (req, res) => {
    const username = req.params.username;
    const password = req.params.password;
    var result;
    result = await usuariosModel.validarUsuario(username, password) ;
    res.json(result);
});

router.post('/login', async (req, res) => {
    const { username, password } = req.body;

    // Agrega un log para verificar qué se recibe en la solicitud
    console.log(`Usuario: ${username}, Contraseña: ${password}`);

    const user = await usuariosModel.validarUsuario(username, password);

    console.log(user); // Agrega un log para verificar lo que devuelve la función

    if (user) {
        try {
            const response = await axios.post('http://localhost:3003/create', {
                username
            });
            const cartId = response.data.cartId;
            res.status(200).json({ message: 'Login exitoso', cartId });
        } catch (error) {
            console.error('Error al crear el carrito:', error.message);
            res.status(500).json({ message: 'Error interno del servidor' });
        }
    } else {
        res.status(401).json({ error: 'Credenciales inválidas' });
    }
});




module.exports = router;