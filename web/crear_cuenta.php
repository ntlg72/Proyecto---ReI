<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - MI PUNTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5 0%, #9face6 100%);
            font-family: 'Roboto', sans-serif;
            color: #333;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        h2 {
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2.5rem;
            text-align: center;
            color: #007bff;
        }

        label {
            font-weight: 500;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: scale(1.05);
        }

        .text-danger,
        .text-success {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .response-message {
            margin-top: 20px;
        }

        @media (max-width: 576px) {
            .container {
                padding: 20px;
                width: 90%;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Crear Cuenta</h2>
        <form id="registerForm" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="customer_city" class="form-label">Ciudad</label>
                <input type="text" class="form-control" id="customer_city" name="customer_city" required>
            </div>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" required>
            </div>
            <div class="mb-3">
                <label for="documento_de_identidad" class="form-label">Documento de Identidad</label>
                <input type="text" class="form-control" id="documento_de_identidad" name="documento_de_identidad" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Registrar</button>
        </form>

        <!-- Botones alineados uno al lado del otro -->
        <div class="mt-4 d-flex justify-content-between">
            <a href="index.html" class="btn btn-secondary"><i class="fas fa-sign-in-alt"></i> Ingresar</a>
        </div>

        <div id="responseMessage" class="mt-3 response-message">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'nombre' => $_POST['nombre'],
                    'password' => $_POST['password'],
                    'customer_city' => $_POST['customer_city'],
                    'direccion' => $_POST['direccion'],
                    'documento_de_identidad' => $_POST['documento_de_identidad']
                ];

                $options = [
                    'http' => [
                        'header'  => "Content-Type: application/json\r\n",
                        'method'  => 'POST',
                        'content' => json_encode($data),
                    ],
                ];

                $context  = stream_context_create($options);
                $result = @file_get_contents('http://localhost:3001/usuarios/crear', false, $context);

                if ($result === FALSE) {
                    echo '<div class="text-danger">Error al intentar crear la cuenta</div>';
                } else {
                    // Verificar si $result no es vacío y se puede decodificar
                    $response = json_decode($result, true);
                    if (is_array($response) && isset($response['success'])) {
                        if ($response['success']) {
                            echo '<div class="text-success">' . htmlspecialchars($response['message']) . '</div>';
                        } else {
                            echo '<div class="text-danger">' . htmlspecialchars($response['message']) . '</div>';
                        }
                    } else {
                        echo '<div class="text-danger">Respuesta inesperada del servidor.</div>';
                    }
                }
            }
            ?>
        </div>
    </div>
</body>

</html>
