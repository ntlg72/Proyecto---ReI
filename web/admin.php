<?php
// Verifica si el usuario ha iniciado sesión
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ingresar.php');
    exit();
}

// Obtener el nombre de usuario
$username = $_SESSION['username'];

// Llamar a la API de productos para obtener la lista de productos
$urlProductos = "http://localhost:3002/productos"; // URL de tu API de productos
$response = file_get_contents($urlProductos);

if ($response === false) {
    die('Error al obtener los productos');
}

// Decodificar la respuesta JSON
$productos = json_decode($response, true);

// Obtener categorías desde la API
$urlCategorias = "http://localhost:3002/categorias"; // URL de tu API de categorías
$responseCategorias = file_get_contents($urlCategorias);

if ($responseCategorias === false) {
    die('Error al obtener las categorías');
}

$categorias = json_decode($responseCategorias, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error al decodificar JSON: ' . json_last_error_msg());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@700&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
            font-family: 'Poppins', sans-serif;
        }
        h1, .card-header {
            font-family: 'Open Sans', sans-serif;
            color: #5086c1;
            margin-bottom: 30px;
        }
        .container {
            margin-top: 40px;
        }
        .card-header {
            background-color: #5086c1;
            color: white;
            font-size: 18px;
        }
        .card-body {
            background-color: #ffffff;
        }
        .btn-admin {
            background-color: #5086c1;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            margin: 10px;
            display: inline-flex;
            align-items: center;
        }
        .btn-admin:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            margin: 10px;
            display: inline-flex;
            align-items: center;
        }
        .btn-danger:hover {
            background-color: #510000; /* Color en hover */
        }
        .btn-warning {
            background-color: #96c4c4;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            margin: 10px;
            display: inline-flex;
            align-items: center;
        }
        .btn-warning:hover {
            background-color: #4d7979; /* Color en hover */
        }
        .btn-secondary {
            background-color: #1b4275;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            margin: 10px;
            display: inline-flex;
            align-items: center;
        }
        .btn-secondary:hover {
            background-color: #002959; /* Color en hover */
        }
        .btn-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 8px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .modal-content {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Panel de Administración</h1>
        <div class="btn-container text-center">
            <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto"><i class="fas fa-plus"></i> Añadir Producto</button>
            <a href="admin-facturas.php" class="btn btn-admin"><i class="fas fa-file-invoice"></i> Ver Facturas</a>
            <a href="admin-estadisticas.php" class="btn btn-admin"><i class="fas fa-chart-line"></i> Ver Estadísticas</a>
            <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
        <!-- Barra de búsqueda por ID -->
        <div class="mb-3">
            <form method="post" action="">
                <label for="searchProductId" class="form-label">Buscar Producto por ID</label>
                <input type="text" class="form-control" id="searchProductId" name="searchProductId" placeholder="Ingresa el ID del producto" required>
                <button type="submit" class="btn btn-primary mt-2"><i class="fas fa-search"></i> Buscar</button>
            </form>
        </div>           
        <?php
        if (isset($_POST['searchProductId'])) {
            $product_id = htmlspecialchars($_POST['searchProductId']);
            
            // Llamar API para obtener producto
            $productos_url = "http://localhost:3002/productos/$product_id";
            $curl = curl_init($productos_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            // Decodificar la respuesta JSON
            $producto = json_decode($response, true);

            // Verifica si el producto fue encontrado
            if ($http_status == 200 && $producto) {
                // Mostrar los detalles del producto y agregar botones de Eliminar y Actualizar
                echo '<div id="searchResult" class="card mb-3">';
                echo '  <div class="card-header">Resultado de la Búsqueda</div>';
                echo '  <div class="card-body">';
                echo '      <p><strong>ID:</strong> ' . htmlspecialchars($producto['product_id']) . '</p>';
                echo '      <p><strong>Categoría:</strong> ' . htmlspecialchars($producto['product_category']) . '</p>';
                echo '      <p><strong>Nombre:</strong> ' . htmlspecialchars($producto['product_name']) . '</p>';
                echo '      <p><strong>Stock:</strong> ' . htmlspecialchars($producto['product_stock']) . '</p>';
                echo '      <p><strong>Precio Unitario (COP):</strong> ' . htmlspecialchars($producto['unit_price_cop']) . '</p>';
                echo '      <div class="d-flex justify-content-between">';
                echo '          <a href="confirmar-eliminar.php?id=' . htmlspecialchars($producto['product_id']) . '" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Eliminar</a>';
                echo '          <a href="editar-producto.php?id=' . htmlspecialchars($producto['product_id']) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Editar</a>';
                echo '      </div>';
                echo '  </div>';
                echo '</div>';
            } else {
                // Producto no encontrado
                echo '<div id="searchResult" class="card mb-3">';
                echo '  <div class="card-header">Resultado de la Búsqueda</div>';
                echo '  <div class="card-body">';
                echo '      <p>Producto no encontrado.</p>';
                echo '  </div>';
                echo '</div>';
            }
        }
        ?>
        <div class="mb-3">
            <form method="post" action="">
                <div class="input-group">
                    <select class="form-select" id="searchProductCategory" name="selectedCategory" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo htmlspecialchars($categoria['product_category']); ?>"><?php echo htmlspecialchars($categoria['product_category']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                </div>
            </form>
        </div>

        <?php
        // Botón para volver a ver todos los productos
        echo '<div class="mb-3 text-center">';
        echo '<a href="admin.php" class="btn btn-secondary"><i class="fas fa-th-list"></i> Ver todos los productos</a>';
        echo '</div>';

        // Filtrar productos por categoría seleccionada
        if (isset($_POST['selectedCategory'])) {
            $selected_category = htmlspecialchars($_POST['selectedCategory']);
            $productos_url = "http://localhost:3002/productos/categoria/$selected_category"; 
            $response = file_get_contents($productos_url);
            $productos = json_decode($response, true);

            if ($productos) {
                echo '<h2>Productos en la categoría: ' . htmlspecialchars($selected_category) . '</h2>';
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
                            <a href="confirmar-eliminar.php?id=' . htmlspecialchars($producto['product_id']) . '" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Eliminar</a>
                            <a href="editar-producto.php?id=' . htmlspecialchars($producto['product_id']) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Editar</a>
                        </td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<p>No se encontraron productos.</p>';
            }
        } else {
        ?>
        <!-- Sección de Productos -->
        <div class="card mb-3">
            <div class="card-header">Productos Disponibles</div>
            <div class="card-body">
                <?php
                // URL del servicio API para obtener los productos
                $productos_url = "http://localhost:3002/productos/desc";
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
                                <a href="confirmar-eliminar.php?id=' . htmlspecialchars($producto['product_id']) . '" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Eliminar</a>
                                <a href="editar-producto.php?id=' . htmlspecialchars($producto['product_id']) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Editar</a>
                            </td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<p>No se encontraron productos.</p>';
                }
                ?>
            </div>
        </div>
        <?php } ?>
        <!-- Modal para añadir un nuevo producto -->
        <div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-labelledby="modalAgregarProductoLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalAgregarProductoLabel">Añadir Nuevo Producto</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <div class="mb-3">
                                <label for="urlimagen" class="form-label">URL de la Imagen</label>
                                <input type="text" class="form-control" id="urlimagen" name="urlimagen" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Añadir Producto</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





