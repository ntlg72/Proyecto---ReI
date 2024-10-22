<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ingresar.php');
    exit();
}

$username = $_SESSION['username'];

// Reemplazar ':username' con el nombre de usuario en la URL para obtener los items del carrito
$urlCarritoItems = "http://192.168.100.2:3003/carrito/items/" . urlencode($username);

// Configurar opciones de contexto para manejo de errores
$options = [
    'http' => [
        'method'  => 'GET',
        'header'  => 'Content-Type: application/json',
        'timeout' => 30, // Tiempo de espera en segundos
    ]
];
$context = stream_context_create($options);

// Llamar a la API de carritos para obtener los productos del carrito
$responseItems = @file_get_contents($urlCarritoItems, false, $context);

// Manejar errores al obtener el carrito
if ($responseItems === false) {
    $error = error_get_last();
    die('Error al obtener el carrito: ' . $error['message']);
}

// Decodificar la respuesta JSON
$carritoItems = json_decode($responseItems, true);

// Manejar errores al decodificar JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error al decodificar JSON: ' . json_last_error_msg());
}

// Obtener carrito_id de los items
if (isset($carritoItems['items']) && !empty($carritoItems['items'])) {
    $carrito_id = $carritoItems['items'][0]['carrito_id']; // Asumimos que todos los items pertenecen al mismo carrito
} else {
    die('No se encontró ningún producto en el carrito.');
}

// Ahora llama a la API de carritos para obtener el subtotal, precioEnvio y total utilizando el carrito_id
$urlCarritoDetails = "http://192.168.100.2:3003/carritos/" . urlencode($carrito_id);
$responseDetails = @file_get_contents($urlCarritoDetails, false, $context);

if ($responseDetails === false) {
    $error = error_get_last();
    die('Error al obtener detalles del carrito: ' . $error['message']);
}

// Decodificar la respuesta JSON
$carritoDetails = json_decode($responseDetails, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error al decodificar JSON de detalles: ' . json_last_error_msg());
}

// Combinar los resultados de los items y los detalles
$carrito = [
    'items' => $carritoItems['items'],
    'subtotal' => $carritoDetails['subtotal'],
    'precioEnvio' => $carritoDetails['precioEnvio'],
    'total' => $carritoDetails['total'],
];

// Función para eliminar un producto del carrito
if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $product_id = $_POST['product_id'];
    
    $urlEliminar = "http://192.168.100.2:3003/carrito/eliminar";
    
    $data = [
        'username' => $username,
        'productId' => $product_id
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'DELETE',
            'content' => json_encode($data),
            'ignore_errors' => true,
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($urlEliminar, false, $context);
    
    if ($result === false) {
        $error = error_get_last();
        echo 'Error al eliminar el producto del carrito: ' . $error['message'];
    } else {
        header('Location: ver-carrito.php');
        exit();
    }
}

// Función para vaciar el carrito
if (isset($_POST['action']) && $_POST['action'] === 'vaciar') {
    $urlVaciar = "http://192.168.100.2:3003/carrito/vaciar";
    
    $data = [
        'cartId' => $carrito_id
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'DELETE',
            'content' => json_encode($data),
            'ignore_errors' => true,
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($urlVaciar, false, $context);
    
    if ($result === false) {
        echo 'Error al vaciar el carrito';
    } else {
        header('Location: ver-carrito.php');
        exit();
    }
}

// Función para modificar la cantidad de un producto en el carrito
if (isset($_POST['action']) && $_POST['action'] === 'modificar') {
    $product_id = $_POST['product_id'];
    $cantidad = $_POST['cantidad'];
    
    if (!is_numeric($cantidad) || $cantidad <= 0) {
        echo 'Cantidad inválida';
        exit();
    }
    
    $urlModificar = "http://192.168.100.2:3003/carrito/actualizar";
    
    $data = [
        'username' => $username,
        'product_id' => $product_id,
        'quantity' => $cantidad
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
    $result = file_get_contents($urlModificar, false, $context);
    
    if ($result === false) {
        echo 'Error al modificar la cantidad del producto';
    } else {
        header('Location: ver-carrito.php');
        exit();
    }
}

// Función para crear la factura
if (isset($_POST['action']) && $_POST['action'] === 'facturar') {
    $urlFacturar = "http://192.168.100.2:3003/factura/crear";
    
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
                echo 'Factura creada exitosamente.<br>';
                echo 'Número de Factura: ' . htmlspecialchars($factura['id_factura']) . '<br>';

                // Mostrar otros detalles de la factura si están disponibles
                echo 'Usuario: ' . htmlspecialchars($factura['user_id'] ?? 'No disponible') . '<br>';
                echo 'Email: ' . htmlspecialchars($factura['email'] ?? 'No disponible') . '<br>';
                echo 'Nombre: ' . htmlspecialchars($factura['nombre'] ?? 'No disponible') . '<br>';
                echo 'Ciudad: ' . htmlspecialchars($factura['ciudad'] ?? 'No disponible') . '<br>';
                echo 'Dirección: ' . htmlspecialchars($factura['direccion'] ?? 'No disponible') . '<br>';
                echo 'Documento de Identidad: ' . htmlspecialchars($factura['documento_identidad'] ?? 'No disponible') . '<br>';
                echo 'Subtotal: ' . htmlspecialchars($factura['subtotal'] ?? '0') . ' COP<br>';
                echo 'Precio de Envío: ' . htmlspecialchars($factura['precio_envio'] ?? '0') . ' COP<br>';
                echo 'Total: ' . htmlspecialchars($factura['total'] ?? '0') . ' COP<br>';
                echo 'Fecha: ' . htmlspecialchars($factura['fecha'] ?? 'No disponible') . '<br>';
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
    <title>Ver Carrito</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .carrito {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        button {
            padding: 8px 16px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            color: white;
            background-color: #3498db;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #2980b9;
        }
        input[type="number"] {
            width: 60px;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .totales {
            background-color: #ecf0f1;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        .totales h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .actions {
            margin-top: 20px;
            text-align: right;
        }
        .actions button {
            margin-left: 10px;
        }
        .empty-cart {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #7f8c8d;
        }
        .factura-creada {
            background-color: #2ecc71;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .carrito-vacio {
            background-color: #3498db;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Carrito de Compras</h1>
    
    <?php
    // Mostrar mensaje de factura creada si es necesario
    if (isset($_POST['action']) && $_POST['action'] === 'facturar' && $result !== false) {
        echo '<div class="factura-creada">¡Factura Creada con Éxito!</div>';
    }
    ?>

    <div class="carrito">
        <?php if (!empty($carrito['items'])): ?>
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
                                <td><?php echo htmlspecialchars($item['precio']); ?> COP</td>
                                <td>
                                    <input type="number" name="cantidad" value="<?php echo htmlspecialchars($item['cantidad']); ?>" min="1" max="1000">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['producto_id']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($item['cantidad'] * $item['precio']); ?> COP</td>
                                <td>
                                    <button type="submit" name="action" value="modificar">Modificar</button>
                                    <button type="submit" name="action" value="eliminar">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>

            <div class="totales">
                <h3>Resumen del Pedido</h3>
                <p><strong>Subtotal:</strong> <?php echo isset($carrito['subtotal']) ? htmlspecialchars($carrito['subtotal']) : '0'; ?> COP</p>
                <p><strong>Precio de Envío:</strong> <?php echo isset($carrito['precioEnvio']) ? htmlspecialchars($carrito['precioEnvio']) : '0'; ?> COP</p>
                <p><strong>Total:</strong> <?php echo isset($carrito['total']) ? htmlspecialchars($carrito['total']) : '0'; ?> COP</p>
            </div>

            <div class="actions">
                <form action="ver-carrito.php" method="POST">
                    <button type="submit" name="action" value="vaciar">Vaciar Carrito</button>
                    <button type="submit" name="action" value="facturar">Crear Factura</button>
                </form>
            </div>

        <?php else: ?>
            <div class="carrito-vacio">
                <h2>Tu Carrito está Vacío</h2>
                <p>¡Agrega algunos productos y comienza a comprar!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
