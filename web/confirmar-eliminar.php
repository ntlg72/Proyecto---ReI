<?php
if (isset($_GET['id'])) {
    $product_id = htmlspecialchars($_GET['id']);
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmar Eliminación</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f1f5f9;
                color: #212529;
                font-family: Arial, sans-serif;
            }
            .container {
                margin-top: 40px;
            }
            .btn-danger {
                background-color: #dc3545;
                border: none;
                padding: 10px 20px;
                text-decoration: none;
                color: white;
            }
            .btn-danger:hover {
                background-color: #c82333;
            }
            .btn-secondary {
                background-color: #6c757d;
                padding: 10px 20px;
                text-decoration: none;
                color: white;
            }
            .btn-secondary:hover {
                background-color: #5a6268;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <div class="card-header bg-danger text-white">Confirmar Eliminación</div>
                <div class="card-body">
                    <p>¿Estás seguro de que deseas eliminar el producto con ID <strong><?php echo $product_id; ?></strong>?</p>
                    <a href="eliminar-producto.php?id=<?php echo $product_id; ?>" class="btn btn-danger">Eliminar</a>
                    <a href="admin.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </body>
    </html>

    <?php
} else {
    echo "ID de producto no proporcionado.";
}
?>
