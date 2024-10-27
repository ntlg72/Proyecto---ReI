<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito Vacío</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .carrito-vacio {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #333;
        }

        p {
            color: #666;
            font-size: 16px;
        }

        .btn-retroceder {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-retroceder:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="carrito-vacio">
        <h2>Tu Carrito está Vacío</h2>
        <p>¡Agrega algunos productos y comienza a comprar!</p>
        <a href="usuario.php" class="btn-retroceder">Volver al Catalogo de Productos</a>
    </div>

</body>
</html>
