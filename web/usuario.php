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
    <title>Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #f0f4f8, #cfd8dc);
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            color: #2c3e50;
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 30px;
        }

        /* Navigation Icons */
        .nav-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .nav-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background-color: #1976d2;
            color: white;
            border-radius: 50%;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s, background-color 0.3s;
        }

        .nav-icon:hover {
            background-color: #1565c0;
            transform: scale(1.1);
        }

        /* Search Bar and View All Button Container */
        .search-bar-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px; /* Increased gap for more space */
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .search-form select {
            height: 45px;
            padding: 0 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1rem;
            font-family: 'Nunito', sans-serif;
            min-width: 200px; /* Set a minimum width */
        }

        .view-all-btn {
            background-color: #1976d2;
            color: white;
            padding: 12px 25px;
            font-weight: bold;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: background-color 0.3s, transform 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-all-btn:hover {
            background-color: #1565c0;
            transform: scale(1.05);
        }

        /* Product Grid */
        .productos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 0 20px;
        }

        /* Product Card */
        .producto {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }

        .producto:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }

        .producto img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .producto h3 {
            font-family: 'Poppins', sans-serif;
            color: #34495e;
            font-size: 1.2rem;
            margin: 10px 0;
            font-weight: 600;
        }

        .producto p {
            color: #7f8c8d;
            font-size: 1rem;
            margin-bottom: 15px;
        }

        /* Quantity Selector */
        .cantidad-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
        }

        .cantidad-container button {
            background-color: #74a7e4;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .cantidad-container button:hover {
            background-color: #336ca5;
        }

        .cantidad-container input {
            width: 50px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-align: center;
            font-size: 1rem;
        }

        /* Add to Cart Button */
        .producto button {
            margin-top: 20px;
            padding: 10px;
            width: 100%;
            background-color: #74a7e4;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .producto button:hover {
            background-color: #336ca5;
        }
    </style>
</head>

<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($username); ?> a nuestra tienda de productos</h1>

    <div class="nav-buttons">
        <a href="ver-carrito.php" class="nav-icon" title="Ver carrito">
            <span class="material-icons">shopping_cart</span>
        </a>
        <a href="logout.php" class="nav-icon" title="Cerrar sesión">
            <span class="material-icons">logout</span>
        </a>
    </div>

    <div class="search-bar-container">
        <form method="post" action="" class="search-form d-flex align-items-center">
            <select class="form-select" id="searchProductCategory" name="selectedCategory" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo htmlspecialchars($categoria['product_category']); ?>"><?php echo htmlspecialchars($categoria['product_category']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary d-flex align-items-center" style="margin-left: 10px;">
                <span class="material-icons">search</span>
            </button>
        </form>

        <!-- Nuevo formulario para buscar por nombre de producto -->
        <form method="post" action="" class="search-form d-flex align-items-center">
                <input type="text" id="searchProductName" name="searchProductName" placeholder="Buscar por nombre" class="form-control" required style="min-width: 200px;">
                <button type="submit" class="btn btn-primary d-flex align-items-center" style="margin-left: 10px;">
                    <span class="material-icons">search</span>
                </button>
            </form>

            <a href="usuario.php" class="view-all-btn">
                <span class="material-icons">view_list</span> Ver todos los productos
            </a>
    </div>


    <?php
    // Initialize products array
    $productos = [];

    // Filtrar productos por categoría seleccionada
    if (isset($_POST['selectedCategory']) && $_POST['selectedCategory'] !== "") {
        $selected_category = htmlspecialchars($_POST['selectedCategory']);
        $productos_url = "http://localhost:3002/productos/categoria/$selected_category"; 
        $response = file_get_contents($productos_url);
        $productos = json_decode($response, true);

        if ($productos) {
            echo '<h2>Productos en la categoría: ' . htmlspecialchars($selected_category) . '</h2>';
        } else {
            echo '<p>No se encontraron productos en esta categoría.</p>';
        }
    }
    // Filtrar productos por nombre
    if (isset($_POST['searchProductName']) && $_POST['searchProductName'] !== "") {
        $search_product_name = trim(htmlspecialchars($_POST['searchProductName'])); // Usar trim para quitar espacios


        $encoded_product_name = urlencode($search_product_name); // Codificar el nombre del producto
        
        // Reemplazar '+' por '%20'
        $encoded_product_name = str_replace('+', '%20', $encoded_product_name);

        $productos_url = "http://localhost:3002/productos/nombre/$encoded_product_name"; 

        $response = file_get_contents($productos_url);
        
        // Verificar si la respuesta es falsa
        if ($response === false) {
            echo '<p>Error al hacer la solicitud a la API.</p>';
        } else {
            // Ver la respuesta cruda para depuración
            echo "Respuesta de la API: $response<br>";

            // Decodificar la respuesta JSON
            $producto = json_decode($response, true);
            
            // Validar si json_decode no devolvió un error
            if (json_last_error() === JSON_ERROR_NONE) {
                // Asegúrate de que la respuesta sea un array asociativo
                if (isset($producto['product_id'])) {
                    echo '<h2>Resultados para: ' . htmlspecialchars($search_product_name) . '</h2>';
                    echo '<div class="productos">';
                    
                    echo '<div class="producto">';
                    echo '<img src="' . htmlspecialchars($producto['product_url']) . '" alt="' . htmlspecialchars($producto['product_name']) . '">';
                    echo '<h3>' . htmlspecialchars($producto['product_name']) . '</h3>';
                    echo '<p>Precio: ' . htmlspecialchars($producto['unit_price_cop']) . ' COP</p>';
                    
                    echo '<form action="agregar-carrito.php" method="POST">';
                    echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($producto['product_id']) . '">';
                    echo '<div class="cantidad-container">';
                    echo '<button type="button" onclick="changeQuantity(' . htmlspecialchars($producto['product_id']) . ', -1)">-</button>';
                    echo '<input type="number" id="cantidad_' . htmlspecialchars($producto['product_id']) . '" name="cantidad" value="1" min="1" max="1000" readonly>';
                    echo '<button type="button" onclick="changeQuantity(' . htmlspecialchars($producto['product_id']) . ', 1)">+</button>';
                    echo '</div>';
                    echo '<button type="submit">Agregar al carrito</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<p>No se encontraron productos con ese nombre.</p>';
                }
            } else {
                echo '<p>Error al procesar la respuesta de la API. Detalles: ' . json_last_error_msg() . '</p>';
            }
        }
    }






    // Fetch and display all products if the "Ver todos los productos" button is clicked
    if (isset($_GET['view_all']) || (empty($_POST['selectedCategory']) && empty($productos))) {
        $productos_url = "http://localhost:3002/productos"; // URL to fetch all products
        $response = file_get_contents($productos_url);
        $productos = json_decode($response, true);

        if ($productos) {
            echo '<h2>Todos los productos</h2>';
        } else {
            echo '<p>No se encontraron productos disponibles.</p>';
        }
    }

    // Display the products
    if ($productos) {
        echo '<div class="productos">';
        foreach ($productos as $producto) {
            echo '<div class="producto">';
            echo '<img src="' . htmlspecialchars($producto['product_url']) . '" alt="' . htmlspecialchars($producto['product_name']) . '">';
            echo '<h3>' . htmlspecialchars($producto['product_name']) . '</h3>';
            echo '<p>Precio: ' . htmlspecialchars($producto['unit_price_cop']) . ' COP</p>';
            echo '<form action="agregar-carrito.php" method="POST">';
            echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($producto['product_id']) . '">';
            echo '<div class="cantidad-container">';
            echo '<button type="button" onclick="changeQuantity(' . htmlspecialchars($producto['product_id']) . ', -1)">-</button>';
            echo '<input type="number" id="cantidad_' . htmlspecialchars($producto['product_id']) . '" name="cantidad" value="1" min="1" max="1000" readonly>';
            echo '<button type="button" onclick="changeQuantity(' . htmlspecialchars($producto['product_id']) . ', 1)">+</button>';
            echo '</div>';
            echo '<button type="submit">Agregar al carrito</button>';
            echo '</form>';
            echo '</div>';
        }
        echo '</div>';
    }
    ?>

    <script>
        function changeQuantity(productId, amount) {
            const quantityInput = document.getElementById('cantidad_' + productId);
            let currentValue = parseInt(quantityInput.value);
            currentValue = isNaN(currentValue) ? 1 : currentValue;
            quantityInput.value = Math.max(1, currentValue + amount);
        }
    </script>
</body>

</html>
