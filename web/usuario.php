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
$urlProductos = "http://192.168.100.2:3002/productos"; // URL de tu API de productos
$response = file_get_contents($urlProductos);

if ($response === false) {
    die('Error al obtener los productos');
}

// Decodificar la respuesta JSON
$productos = json_decode($response, true);

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
    <link rel="stylesheet" href="styles.css"> <!-- Asegúrate de agregar tu hoja de estilos -->
    <style>
        /* Reset básico para asegurar consistencia en navegadores */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            padding: 20px;
        }

        h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 2rem;
            color: #16a085;
            text-align: center;
            margin-bottom: 20px;
        }

        h3 {
            font-size: 1.2rem;
            color: #34495e;
            margin-bottom: 10px;
        }

        /* Botón Ver Carrito */
        .btn-carrito {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        .btn-carrito:hover {
            background-color: #2980b9;
        }

        /* Contenedor de productos */
        .productos {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
        }

        .producto {
            background-color: white;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            width: 250px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }

        .producto:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .producto p {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        .producto form {
            margin-top: 10px;
        }

        .producto label {
            font-size: 0.9rem;
            color: #2c3e50;
        }

        .producto input[type="number"] {
            width: 60px;
            padding: 5px;
            margin-left: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .producto button {
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #16a085;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .producto button:hover {
            background-color: #1abc9c;
        }

    </style>
</head>
<body>

    <!-- Título de bienvenida -->
    <h1>Bienvenido, <?php echo htmlspecialchars($username); ?></h1>

    <!-- Botón Ver Carrito -->
    <a href="ver-carrito.php" class="btn-carrito">Ver Carrito</a>

    <!-- Mostrar productos -->
    <h2>Productos disponibles</h2>
    <div class="productos">
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $producto): ?>
                <div class="producto">
                    <h3><?php echo htmlspecialchars($producto['product_name']); ?></h3>
                    <p>Precio: <?php echo htmlspecialchars($producto['unit_price_cop']); ?> COP</p>
                    <form action="agregar-carrito.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($producto['product_id']); ?>">
                        <label for="cantidad_<?php echo htmlspecialchars($producto['product_id']); ?>">Cantidad:</label>
                        <input type="number" id="cantidad_<?php echo htmlspecialchars($producto['product_id']); ?>" name="cantidad" value="1" min="1" max="1000">
                        <button type="submit">Agregar al carrito</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay productos disponibles.</p>
        <?php endif; ?>
    </div>

</body>
</html>
