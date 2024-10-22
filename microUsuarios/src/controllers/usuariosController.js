const { Router } = require('express');
const router = Router();
const axios = require('axios');
const usuariosModel = require('../models/usuariosModel');


router.post('/usuarios', async (req, res) => {
    const username = req.body.username;
    const email = req.body.email;
    const nombre = req.body.nombre;
    const password = req.body.password;
    const customer_city = req.body.customer_city;
    const direccion = req.body.direccion;
    const documento_de_identidad = req.body.documento_de_identidad;
    
    var result = await usuariosModel.crearUsuario(username,email,nombre,password,customer_city,direccion,documento_de_identidad);
    res.send("usuario creado");
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

    // Validar las credenciales del usuario
    const user = await usuariosModel.validarUsuario(username, password); // Implementa esta función de validación

    if (user) {
        try {
            // Hacer la solicitud a la API que contiene createCartIfNotExists
            const response = await axios.post('http://192.168.100.2:3003/create', {
                username
            });

            // Obtener el ID del carrito desde la respuesta de la API
            const cartId = response.data.cartId;

            res.status(200).json({ message: 'Login exitoso', cartId });
        } catch (error) {
            console.error('Error al crear el carrito:', error.message);
            res.status(500).json({ message: 'Error interno del servidor' });
        }
    } else {
        res.status(401).json({ message: 'Credenciales inválidas' });
    }
});



module.exports = router;