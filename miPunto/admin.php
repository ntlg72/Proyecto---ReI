<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
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
        .card-header {
            background-color: #007BFF;
            color: white;
            font-size: 18px;
        }
        .card-body {
            background-color: #ffffff;
        }
        .btn-admin {
            background-color: #007BFF;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 16px;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-admin:hover {
            background-color: #0056b3;
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
        .btn-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        h1 {
            color: #007BFF;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Panel de Administración</h1>
        <div class="btn-container text-center">
            <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">Añadir Producto</button>
            <a href="admin-facturas.php" class="btn btn-admin">Ver Facturas</a>
            <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>

        <!-- Sección de Productos -->
        <div class="card mb-3">
            <div class="card-header">Productos Disponibles</div>
            <div class="card-body">
                <?php
                // URL del servicio API para obtener los productos
                $productos_url = "http://192.168.100.2:3002/productos";
                $curl = curl_init($productos_url);

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                curl_close($curl);

                // Decodificar la respuesta JSON en un arreglo
                $productos = json_decode($response, true);

                if (is_array($productos) && count($productos) > 0) {
                    echo '<table class="table table-striped">';
                    echo '<thead><tr><th>ID</th><th>Categoría</th><th>Nombre</th><th>Stock</th><th>Precio Unitario (COP)</th><th>Acciones</th></tr></thead>';
                    echo '<tbody>';
                    foreach ($productos as $producto) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($producto['product_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($producto['product_category']) . '</td>';
                        echo '<td>' . htmlspecialchars($producto['product_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($producto['product_stock']) . '</td>';
                        echo '<td>' . htmlspecialchars($producto['unit_price_cop']) . '</td>';
                        echo '<td>
                                <a href="editar-producto.php?id=' . htmlspecialchars($producto['product_id']) . '" class="btn btn-sm btn-warning">Editar</a>
                                <a href="eliminar-producto.php?id=' . htmlspecialchars($producto['product_id']) . '" class="btn btn-sm btn-danger">Eliminar</a>
                              </td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<p>No hay productos disponibles.</p>';
                }
                ?>
            </div>
        </div>

        <!-- Modal para añadir un nuevo producto -->
        <div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-labelledby="modalAgregarProductoLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarProductoLabel">Añadir Nuevo Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="agregar-producto.php" method="POST">
                            <div class="mb-3">
                                <label for="category" class="form-label">Categoría del Producto</label>
                                <input type="text" class="form-control" id="category" name="category" required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre del Producto</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" required>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Precio Unitario (COP)</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Añadir Producto</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios para Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>





