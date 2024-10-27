<?php
// editar-producto.php
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    // Obtener los datos del producto desde la base de datos o API
    $productos_url = "http://localhost:3002/productos/$product_id";
    $curl = curl_init($productos_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    $producto = json_decode($response, true);

    if (!$producto) {
        echo "Producto no encontrado.";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Actualizar stock
        $nuevo_stock = $_POST['stock'];
        
        // Enviar actualización a la API
        $data = array("product_stock" => $nuevo_stock);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'PUT',
                'content' => json_encode($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($productos_url, false, $context);

        if ($result) {
            header("Location: admin.php");
        } else {
            echo "Error al actualizar el producto.";
        }
    }
}
?>

<!-- Formulario para editar stock del producto -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Producto: <?= htmlspecialchars($producto['product_name']) ?></h2>
        <form method="POST">
            <div class="mb-3">
                <label for="stock" class="form-label">Nuevo Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($producto['product_stock']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
