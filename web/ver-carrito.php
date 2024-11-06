<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ingresar.php');
    exit();
}

$username = $_SESSION['username'];

// Obtener items del carrito de la API
$urlCarritoItems = "http://localhost:3003/carrito/items/" . urlencode($username);

$options = [
    'http' => [
        'method'  => 'GET',
        'header'  => 'Content-Type: application/json',
        'timeout' => 30,
    ]
];
$context = stream_context_create($options);

$responseItems = @file_get_contents($urlCarritoItems, false, $context);
if ($responseItems === false) {
    $error = error_get_last();
    die('Error al obtener el carrito: ' . $error['message']);
}
$carritoItems = json_decode($responseItems, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error al decodificar JSON: ' . json_last_error_msg());
}

$carrito_id = null;
if (isset($carritoItems['items']) && !empty($carritoItems['items'])) {
    $carrito_id = $carritoItems['items'][0]['carrito_id'];
} else {
    header('Location: carrito-vacio.php');
    exit();
}

// Obtener detalles del carrito
$urlCarritoDetails = "http://localhost:3003/carritos/" . urlencode($carrito_id);
$responseDetails = @file_get_contents($urlCarritoDetails, false, $context);
if ($responseDetails === false) {
    $error = error_get_last();
    die('Error al obtener detalles del carrito: ' . $error['message']);
}
$carritoDetails = json_decode($responseDetails, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error al decodificar JSON de detalles: ' . json_last_error_msg());
}

// Combinar los resultados
$carrito = [
    'items' => $carritoItems['items'],
    'subtotal' => $carritoDetails['subtotal'],
    'precioEnvio' => $carritoDetails['precioEnvio'],
    'total' => $carritoDetails['total'],
];

// Eliminar un producto del carrito
if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $product_id = $_POST['product_id'];
    $urlEliminar = "http://localhost:3003/carrito/eliminar";
    $data = ['username' => $username, 'productId' => $product_id];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'DELETE',
            'content' => json_encode($data),
            'ignore_errors' => true,
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($urlEliminar, false, $context);
    
    if ($result === false) {
        $error = error_get_last();
        echo 'Error al eliminar el producto: ' . $error['message'];
    } else {
        header('Location: ver-carrito.php');
        exit();
    }
}

// Vaciar el carrito
if (isset($_POST['action']) && $_POST['action'] === 'vaciar') {
    $urlVaciar = "http://localhost:3003/carrito/vaciar";
    $data = ['cartId' => $carrito_id];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'DELETE',
            'content' => json_encode($data),
            'ignore_errors' => true,
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($urlVaciar, false, $context);
    
    if ($result === false) {
        echo 'Error al vaciar el carrito';
    } else {
        header('Location: ver-carrito.php');
        exit();
    }
}

// Modificar cantidad de producto
if (isset($_POST['action']) && $_POST['action'] === 'modificar') {
    $product_id = $_POST['product_id'];
    $cantidad = $_POST['cantidad'];
    
    if (!is_numeric($cantidad) || $cantidad <= 0) {
        echo 'Cantidad inválida';
        exit();
    }
    $urlModificar = "http://localhost:3003/carrito/actualizar";
    $data = ['username' => $username, 'product_id' => $product_id, 'quantity' => $cantidad];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true,
        ],
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($urlModificar, false, $context);
    
    if ($result === false) {
        echo 'Error al modificar la cantidad';
    } else {
        header('Location: ver-carrito.php');
        exit();
    }
}

// Función para crear la factura
if (isset($_POST['action']) && $_POST['action'] === 'facturar') {
    $urlFacturar = "http://localhost:3003/factura/crear";
    
    $data = [
        'username' => $username,
        'cartId' => $carrito_id
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true,
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($urlFacturar, false, $context);
    
    if ($result === false) {
        $error = error_get_last();
        echo 'Error al crear la factura: ' . $error['message'];
    } else {
        $factura = json_decode($result, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($factura['id_factura'])) {
                // Aquí puedes almacenar la factura en una variable para mostrarla después
                $facturaCreada = $factura;
            } else {
                echo 'Error: No se recibió el ID de la factura en la respuesta.';
            }
        } else {
            echo 'Error al decodificar la respuesta de la factura: ' . json_last_error_msg();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@600&family=Roboto+Condensed:wght@300;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* General Layout */
        body {
            font-family: 'Roboto Condensed', sans-serif; /* Body font */
            background-color: #e6f7f9; /* Very light turquoise */
            color: #2c3e50; /* Dark blue */
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .container {
            max-width: 1400px;
            background-color: #ffffff; /* White */
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            padding: 30px;
            display: flex;
            gap: 50px;
            width: 100%;
        }
        h1 {
            font-family: 'Dosis', sans-serif; /* Header font */
            text-align: center;
            font-size: 2.5rem;
            color: #3498db; /* Soft blue */
            margin-bottom: 30px;
        }
        /* Product Table */
        .products {
            flex: 2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #84b6f4; /* Light turquoise */
            font-size: 1.2rem;
        }
        table th {
            font-family: 'Dosis', sans-serif; /* Header font for table */
            background-color: #84b6f4; /* Light turquoise */
            color: white;
            font-size: 1.3rem;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .quantity-controls button {
            background-color: #81d4fa; /* Pastel blue */
            border: none;
            color: white;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.2rem;
            transition: transform 0.2s; /* Animation for hover effect */
        }
        .quantity-controls button:hover {
            transform: scale(1.1); /* Slightly increase size on hover */
        }
        /* Buttons */
        .actions {
            display: flex; /* Change to flex */
            gap: 10px; /* Space between buttons */
        }
        .actions button {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 1.1rem;
            transition: transform 0.2s; /* Animation for hover effect */
        }
        .actions button:hover {
            transform: scale(1.1); /* Slightly increase size on hover */
        }
        .actions button.modify {
            background-color: #4dd0e1; /* Light turquoise */
        }
        .actions button.delete {
            background-color: #f44336; /* Soft red */
        }
        /* Totals Section */
        .totals {
            flex: 1;
            background-color: #e4fbfb; /* Light turquoise */
            border-radius: 12px;
            padding: 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .totals h3 {
            font-family: 'Dosis', sans-serif; /* Header font for totals */
            margin-top: 0;
            font-size: 2rem;
            margin-bottom: 15px;
            color: #2c3e50; /* Dark blue */
        }
        .totals p {
            font-size: 1.2rem;
            margin: 15px 0;
        }
        .totals p strong {
            color: #333;
        }
        /* Action Buttons */
        .cart-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
            margin-top: 30px;
        }
        .cart-actions button {
            padding: 12px 20px;
            border-radius: 8px;
            color: #fff;
            border: none;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            margin-bottom: 10px; /* Space between buttons */
            transition: transform 0.2s; /* Animation for hover effect */
        }
        .cart-actions button:hover {
            transform: scale(1.1); /* Slightly increase size on hover */
        }
        .cart-actions .empty-cart {
            background-color: #f44336; /* Soft red */
        }
        .cart-actions .create-invoice {
            background-color: #4dd0e1; /* Light turquoise */
        }
        .cart-actions .back-catalog {
            background-color: #29b6f6; /* Medium blue */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="products">
            <h1>Carrito de Compras</h1>
            <form action="ver-carrito.php" method="POST">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carrito['items'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['producto_id']); ?></td>
                                <td><?php echo number_format($item['precio'], 2); ?> COP</td>
                                <td>
                                    <div class="quantity-controls">
                                        <button type="button" onclick="updateQuantity(this.nextElementSibling, false)">-</button>
                                        <input type="number" name="cantidad" value="<?php echo htmlspecialchars($item['cantidad']); ?>" min="1" max="1000" style="width: 60px; text-align: center; font-size: 1.1rem;" readonly>
                                        <button type="button" onclick="updateQuantity(this.previousElementSibling, true)">+</button>
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['producto_id']); ?>">
                                    </div>
                                </td>
                                <td><?php echo number_format($item['cantidad'] * $item['precio'], 2); ?> COP</td>
                                <td class="actions">
                                    <button type="submit" name="action" value="modificar" class="modify"><span class="material-icons">edit</span></button>
                                    <button type="submit" name="action" value="eliminar" class="delete"><span class="material-icons">delete</span></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>

        <div class="totals">
            <h3>Resumen del Pedido</h3>
            <p><strong>Subtotal:</strong> <?php echo number_format($carrito['subtotal'], 2); ?> COP</p>
            <p><strong>Precio de Envío:</strong> <?php echo number_format($carrito['precioEnvio'], 2); ?> COP</p>
            <p><strong>Total:</strong> <?php echo number_format($carrito['total'], 2); ?> COP</p>

            <div class="cart-actions">
                <form action="ver-carrito.php" method="POST">
                    <button type="submit" name="action" value="vaciar" class="empty-cart"><span class="material-icons">delete_forever</span> Vaciar Carrito</button>
                    <button type="submit" name="action" value="facturar" class="create-invoice"><span class="material-icons">receipt</span> Crear Factura</button>
                    <button type="button" onclick="window.location.href='usuario.php'" class="back-catalog"><span class="material-icons">arrow_back</span> Volver al Catálogo</button>
                </form>
            </div>
        </div>
        <?php if (isset($facturaCreada)): ?>
                <div class="invoice">
                    <h2>Factura Creada</h2>
                    <p><strong>Número de Factura:</strong> <?php echo htmlspecialchars($facturaCreada['id_factura']); ?></p>
                    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($facturaCreada['user_id'] ?? 'No disponible'); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($facturaCreada['email'] ?? 'No disponible'); ?></p>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($facturaCreada['nombre'] ?? 'No disponible'); ?></p>
                    <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($facturaCreada['ciudad'] ?? 'No disponible'); ?></p>
                    <p><strong>Direccion:</strong> <?php echo htmlspecialchars($facturaCreada['direccion'] ?? 'No disponible'); ?></p>
                    <p><strong>Documento Identidad:</strong> <?php echo htmlspecialchars($facturaCreada['documento_identidad'] ?? 'No disponible'); ?></p>
                    <p><strong>Subtotal:</strong> <?php echo htmlspecialchars($facturaCreada['subtotal']); ?> COP</p>
                    <p><strong>Precio de Envío:</strong> <?php echo htmlspecialchars($facturaCreada['precio_envio']); ?> COP</p>
                    <p><strong>Total:</strong> <?php echo htmlspecialchars($facturaCreada['total']); ?> COP</p>
                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars($facturaCreada['fecha'] ?? 'No disponible'); ?></p>
                    <p>Gracias por su compra!</p>
                </div>
            <?php endif; ?>
    </div>

    <script>
        function updateQuantity(input, increment) {
            let quantity = parseInt(input.value);
            if (increment) {
                quantity += 1;
            } else {
                quantity = quantity > 1 ? quantity - 1 : 1; // Prevents quantity from going below 1
            }
            input.value = quantity;
        }
    </script>
</body>
</html>
