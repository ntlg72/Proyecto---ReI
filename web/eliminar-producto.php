<?php
// eliminar-producto.php
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    // Llamar API para eliminar producto
    $productos_url = "http://localhost:3002/productos/$product_id";
    $curl = curl_init($productos_url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    if ($response) {
        header("Location: admin.php");
    } else {
        echo "Error al eliminar el producto.";
    }
}
?>
