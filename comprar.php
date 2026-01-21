<?php
require 'conexion.php';
require 'funciones.php';

procesarAccionesCarrito();

if (!estaLogeado()) {
    redirigir('login.php');
}

$carrito = obtenerCarrito();
$mensaje = 'Accede desde el carrito para comprar';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($carrito)) {
    $stmt = $pdo->prepare('INSERT INTO Compras (id_usuario, id_arma, id_bebida, cantidad) VALUES (?, ?, ?, ?)');

    foreach ($carrito as $item) {
        $idArma = $item['tipo'] === 'arma' ? (int) $item['id'] : null;
        $idBebida = $item['tipo'] === 'bebida' ? (int) $item['id'] : null;
        $stmt->execute([
            $_SESSION['idUsuario'],
            $idArma,
            $idBebida,
            (int) $item['cantidad']
        ]);
    }

    vaciarCarrito();
    $mensaje = 'Compra realizada con éxito';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($carrito)) {
    $mensaje = 'El carrito esta vacío';
}

require 'header.php';
?>

<section class="seccion">
    <h1>Resultado de compra</h1>
    <p class="mensaje ok"><?php echo limpiar($mensaje); ?></p>
    <a href="index.php" class="boton-secundario">Volver al inicio</a>
</section>

<?php require 'footer.php'; ?>
