<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Más Vendidos</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@700&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #e9f1f6;
            color: #212529;
            font-family: 'Poppins', sans-serif;
        }
        h1 {
            color: #5086c1;
            font-size: 2.5em;
            font-family: 'Open Sans', sans-serif;
            text-align: center;
            margin: 30px 0;
        }
        .btn-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-admin, .btn-danger {
            border-radius: 50px;
            padding: 10px 30px;
            font-size: 16px;
            margin: 5px;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }
        .btn-admin {
            background-color: #5086c1;
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-admin:hover {
            background-color: #3b6fa1;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-danger:hover {
            background-color: #b22e37;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            padding: 20px;
            justify-items: center;
        }
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
        .chart-container {
            width: 100%;
            max-width: 700px;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s;
        }
        .chart-container:hover {
            transform: translateY(-5px);
        }
        .chart-title {
            font-size: 1.4em;
            color: #5086c1;
            margin-bottom: 8px;
            font-weight: bold;
            text-align: center;
        }
        img {
            width: 100%;
            border-radius: 10px;
            cursor: pointer;
        }

        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }
        .close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: #ffffff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .close:hover {
            color: #ff0000;
        }
    </style>
</head>
<body>
    <h1>Estadísticas de Ventas</h1>
    <div class="btn-container">
        <a href="admin.php" class="btn btn-admin"><i class="fas fa-box"></i> Gestionar Productos</a>
        <a href="/images/ventas_por_ciudad.html" class="btn btn-admin"><i class="fas fa-city"></i> Ventas por Ciudad</a>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>

    <div class="container">
        <div class="chart-container">
            <h2 class="chart-title">Ventas por Categoría</h2>
            <img src="/images/ventas_por_categoria.png" alt="Ventas por Categoría" class="img-fluid" onclick="openModal(this)">
        </div>
        <div class="chart-container">
            <h2 class="chart-title">Distribución de Productos por Rango de Precio</h2>
            <img src="/images/producto_rango_precio.png" alt="Distribución de Productos por Rango de Precio" class="img-fluid" onclick="openModal(this)">
        </div>
        <div class="chart-container">
            <h2 class="chart-title">Distribución de Frecuencia de Facturas Creadas</h2>
            <img src="/images/frecuencia_facturas.png" alt="Distribución de Frecuencia de Facturas" class="img-fluid" onclick="openModal(this)">
        </div>
        <div class="chart-container">
            <h2 class="chart-title">Top de Productos Más Vendidos</h2>
            <img src="/images/top_productos.png" alt="Top de Productos Más Vendidos" class="img-fluid" onclick="openModal(this)">
        </div>
    </div>

    <!-- Modal para la imagen -->
    <div id="myModal" class="modal" onclick="closeModal()">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="imgModal">
    </div>

    <script>
        // Función para abrir el modal
        function openModal(element) {
            var modal = document.getElementById("myModal");
            var modalImg = document.getElementById("imgModal");
            modal.style.display = "flex";
            modalImg.src = element.src;
        }

        // Función para cerrar el modal
        function closeModal() {
            var modal = document.getElementById("myModal");
            modal.style.display = "none";
        }
    </script>
</body>
</html>
