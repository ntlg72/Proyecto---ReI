<?php
session_start();
// Encabezado para que la respuesta siempre sea en formato JSON
header('Content-Type: application/json');
// Habilitar CORS
header("Access-Control-Allow-Origin: http://www.mipunto.com.co");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Resto de tu código PHP aquí

// Configura la URL de tu API de autenticación
$apiUrl = 'http://192.168.100.2:3001/login';

// Lee los datos JSON enviados desde el cliente
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verifica si los datos fueron decodificados correctamente
if (!is_array($data) || !isset($data['username']) || !isset($data['password'])) {
    http_response_code(400); // Solicitud inválida
    echo json_encode(['message' => 'Solicitud inválida. Datos JSON no válidos.']);
    exit();
}

$username = $data['username'];
$password = $data['password'];

// Crea el array de datos para enviar a la API
$postData = [
    'username' => $username,
    'password' => $password
];

// Inicializa cURL
$ch = curl_init($apiUrl);

// Configura cURL para hacer una solicitud POST
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

// Ejecuta la solicitud
$response = curl_exec($ch);

// Maneja errores de cURL
if (curl_errno($ch)) {
    http_response_code(500); // Error en el servidor
    echo json_encode(['message' => 'Error en la solicitud: ' . curl_error($ch)]);
    curl_close($ch);
    exit();
}

// Cierra cURL
curl_close($ch);

// Decodifica la respuesta de la API
$result = json_decode($response, true);

// Verifica si la respuesta de la API es válida
if (!is_array($result)) {
    http_response_code(502); // Error en la puerta de enlace
    echo json_encode(['message' => 'Respuesta inválida de la API.']);
    exit();
}

// Verifica el resultado y gestiona la redirección
// Simulación de autenticación
if ($username === 'admin') {
    $_SESSION['username'] = $username;
    echo json_encode(['message' => 'Login exitoso', 'redirect' => 'admin.php']);
} else {
    $_SESSION['username'] = $username;
    echo json_encode(['message' => 'Login exitoso', 'redirect' => 'usuario.php']);
}
?>