const { Router } = require('express');
const router = Router();
const productosModel = require('../models/productosModel');

router.get('/productos', async (req, res) => {
    var result;
    result = await productosModel.traerProductos() ;
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

router.post('/productos', async (req, res) => {
    const product_category = req.body.product_category;
    const product_name = req.body.product_name;
    const product_stock = req.body.product_stock;
    const unit_price_cop = req.body.unit_price_cop;

    var result = await productosModel.crearProducto(product_category,product_name,product_stock, unit_price_cop);
    res.send("producto creado");
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