<?php
header('Content-Type: application/json'); // Configura el encabezado para JSON

// Obtén los datos de entrada en formato JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['username']) && isset($data['password'])) {

    $user = $data['username'];
    $pass = $data['password'];

    $servurl = "http://localhost:3001/login";
    $curl = curl_init($servurl);
    
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['username' => $user, 'password' => $pass]));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Configurar el encabezado para JSON
    
    $response = curl_exec($curl);
    
    if ($response === false) {
        echo json_encode(['error' => 'Error en cURL: ' . curl_error($curl)]);
        exit;
    }
    
    curl_close($curl);
    
    $resp = json_decode($response, true);
    
    // Verifica si el backend devuelve éxito
    if (isset($resp['message']) && $resp['message'] === 'Login exitoso') {
        session_start();
        $_SESSION["username"] = $user;

        // Redirigir con respuesta JSON
        if ($user == "admin") { 
            echo json_encode(['redirect' => 'admin.php']);
        } else { 
            echo json_encode(['redirect' => 'usuario.php']);
        }
        exit;
    } else {
        echo json_encode(['error' => 'Credenciales inválidas']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Datos del formulario faltantes']);
    exit;
}
?>

