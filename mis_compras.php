<?php
require 'conexion.php';
require 'funciones.php';

procesarAccionesCarrito();

if (!estaLogeado()) {
    redirigir('login.php');
}

$stmt = $pdo->prepare('SELECT c.*, a.nombre as nombreArma, b.nombre as nombreBebida
    FROM Compras c
    LEFT JOIN Armas a ON c.id_arma = a.id_arma
    LEFT JOIN Bebidas b ON c.id_bebida = b.id_bebida
    WHERE c.id_usuario = ?
    ORDER BY c.fecha_compra DESC');
$stmt->execute([$_SESSION['idUsuario']]);
$compras = $stmt->fetchAll();

require 'header.php';
?>

<section class="seccion">
    <h1>Mis compras</h1>

    <?php if (empty($compras)) : ?>
        <p class="texto-suave">Aún no tienes compras</p>
    <?php else : ?>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $compra) : ?>
                    <?php
                    $nombreProducto = $compra['id_arma'] ? $compra['nombreArma'] : $compra['nombreBebida'];
                    $tipoProducto = $compra['id_arma'] ? 'arma' : 'bebida';
                    ?>
                    <tr>
                        <td><?php echo limpiar($nombreProducto); ?></td>
                        <td><?php echo limpiar($tipoProducto); ?></td>
                        <td><?php echo (int) $compra['cantidad']; ?></td>
                        <td><?php echo limpiar($compra['fecha_compra']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require 'footer.php'; ?>
