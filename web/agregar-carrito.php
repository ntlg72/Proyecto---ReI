<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ingresar.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id']; // Asegúrate de que el nombre del campo coincide con el esperado por la API
    $cantidad = $_POST['cantidad'];

    // Verifica que product_id y cantidad no estén vacíos y sean válidos
    if (empty($product_id) || empty($cantidad) || !is_numeric($cantidad) || $cantidad <= 0) {
        echo 'Datos inválidos';
        exit();
    }

    // Llamar a la API de carritos para agregar el producto
    $username = $_SESSION['username'];
    $urlCarrito = "http://carritos:3003/carrito/add"; // URL de la API de carritos

    // Datos a enviar
    $data = [
        'username' => $username,
        'product' => ['id' => $product_id],
        'quantity' => $cantidad
    ];

    // Configuración de la solicitud POST
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true, // Para capturar errores en el contenido de la respuesta
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($urlCarrito, false, $context);

    // Verifica si hubo un error en la solicitud
    if ($result === false) {
        echo 'Error al enviar la solicitud al carrito';
        exit();
    }

    // Verifica la respuesta de la API
    $http_response_header; // Esto contiene la información de la respuesta HTTP
    if (isset($http_response_header[0]) && strpos($http_response_header[0], '200') === false) {
        echo 'Error en la respuesta de la API: ' . $http_response_header[0];
        exit();
    }

    // Redirigir de nuevo a la página del usuario después de agregar al carrito
    header('Location: usuario.php');
    exit();
}
?>
