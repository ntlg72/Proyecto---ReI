<?php
// crear-producto.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['category'];
    $nombre = $_POST['name'];
    $stock = $_POST['stock'];
    $precio = $_POST['price'];
    $urlimagen = $_POST['urlimagen'];

    // Datos del nuevo producto
    $data = array(
        "product_category" => $categoria,
        "product_name" => $nombre,
        "product_stock" => $stock,
        "unit_price_cop" => $precio,
        "product_url"=> $urlimagen
    );

    // Llamada a la API para crear un nuevo producto
    $productos_url = "http://localhost:3002/productos";
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($productos_url, false, $context);

    if ($result) {
        echo '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Producto Creado</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    color: #333;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                }
                .container {
                    background: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    text-align: center;
                    max-width: 600px;
                    width: 100%;
                }
                h1 {
                    color: #4CAF50;
                }
                p {
                    font-size: 18px;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    font-size: 16px;
                    color: #fff;
                    background-color: #007BFF;
                    border: none;
                    border-radius: 5px;
                    text-decoration: none;
                    cursor: pointer;
                    margin-top: 20px;
                }
                .button:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>¡Producto Creado Exitosamente!</h1>
                <p><strong>Categoría:</strong> ' . htmlspecialchars($categoria) . '</p>
                <p><strong>Nombre:</strong> ' . htmlspecialchars($nombre) . '</p>
                <p><strong>Stock:</strong> ' . htmlspecialchars($stock) . '</p>
                <p><strong>Precio:</strong> ' . htmlspecialchars($precio) . ' COP</p>
                <a href="admin.php" class="button">Volver al Panel de Gestion</a>
            </div>
        </body>
        </html>';
    } else {
        echo "Error al añadir el producto.";
    }
}
?>
