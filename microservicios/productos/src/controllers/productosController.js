const { Router } = require('express');
const router = Router();
const productosModel = require('../models/productosModel');

router.get('/productos', async (req, res) => {
    var result;
    result = await productosModel.traerProductos() ;
    //console.log(result);
    res.json(result);
});

router.get('/productos/desc', async (req, res) => {
    var result;
    result = await productosModel.traerProductosDesc() ;
    //console.log(result);
    res.json(result);
});

router.get('/productos/:product_id', async (req, res) => {
    const product_id = req.params.product_id;
    var result;
    result = await productosModel.traerProducto(product_id) ;
    //console.log(result);
    res.json(result[0]);
});

//Obtener productos por categoría
router.get('/productos/categoria/:product_category', async (req, res) => {
    const product_category = req.params.product_category;
    var result = await productosModel.traerProductosPorCategoria(product_category);
    res.json(result);
});

// En tu controlador productosController.js
router.get('/categorias', async (req, res) => {
    try {
        const categorias = await productosModel.traerCategorias(); // Llama a la nueva función
        res.json(categorias); // Devuelve las categorías en formato JSON
    } catch (error) {
        console.error(error);
        res.status(500).send('Error al obtener las categorías');
    }
});


router.post('/productos', async (req, res) => {
    const product_category = req.body.product_category;
    const product_name = req.body.product_name;
    const product_stock = req.body.product_stock;
    const unit_price_cop = req.body.unit_price_cop;
    const product_url = req.body.product_url;

    var result = await productosModel.crearProducto(product_category,product_name,product_stock, unit_price_cop, product_url);
    res.send("producto creado");
});

router.get('/productos/nombre/:product_name', async (req, res) => {
    const { product_name } = req.params;
    var result;
    result = await productosModel.traerProductosPorNombre(product_name) ;
    //console.log(result);
    res.json(result[0]);
});


router.put('/productos/:product_id', async (req, res) => {
    const product_id = req.params.product_id;
    const product_stock = req.body.product_stock;

    var result = await productosModel.actualizarProducto(product_id, product_stock);
    res.send("inventario actualizado");
});

router.delete('/productos/:product_id', async (req, res) => {
    const product_id = req.params.product_id;
    var result;
    result = await productosModel.borrarProducto(product_id) ;
    //console.log(result);
    res.send("producto borrado");
});

module.exports = router;