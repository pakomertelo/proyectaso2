<?php
require 'conexion.php';
require 'funciones.php';

procesarAccionesCarrito();

$carrito = obtenerCarrito();
$mapaArmas = [];
$mapaBebidas = [];
$idsArmas = [];
$idsBebidas = [];

foreach ($carrito as $item) {
    if ($item['tipo'] === 'arma') {
        $idsArmas[] = (int) $item['id'];
    }
    if ($item['tipo'] === 'bebida') {
        $idsBebidas[] = (int) $item['id'];
    }
}

if (!empty($idsArmas)) {
    $placeholders = implode(',', array_fill(0, count($idsArmas), '?'));
    $stmt = $pdo->prepare("SELECT id_arma, nombre FROM Armas WHERE id_arma IN ($placeholders)");
    $stmt->execute($idsArmas);
    foreach ($stmt as $arma) {
        $mapaArmas[$arma['id_arma']] = $arma['nombre'];
    }
}

if (!empty($idsBebidas)) {
    $placeholders = implode(',', array_fill(0, count($idsBebidas), '?'));
    $stmt = $pdo->prepare("SELECT id_bebida, nombre FROM Bebidas WHERE id_bebida IN ($placeholders)");
    $stmt->execute($idsBebidas);
    foreach ($stmt as $bebida) {
        $mapaBebidas[$bebida['id_bebida']] = $bebida['nombre'];
    }
}

require 'header.php';
?>

<section class="seccion">
    <h1>Carrito completo</h1>

    <?php if (empty($carrito)) : ?>
        <p class="texto-suave">No hay items en el carrito</p>
    <?php else : ?>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($carrito as $item) : ?>
                    <?php
                    $nombreItem = $item['tipo'] === 'arma'
                        ? ($mapaArmas[$item['id']] ?? 'arma desconocida')
                        : ($mapaBebidas[$item['id']] ?? 'bebida desconocida');
                    ?>
                    <tr>
                        <td><?php echo limpiar($nombreItem); ?></td>
                        <td><?php echo limpiar($item['tipo']); ?></td>
                        <td><?php echo (int) $item['cantidad']; ?></td>
                        <td class="fila-acciones">
                            <form method="post">
                                <input type="hidden" name="accion" value="sumar">
                                <input type="hidden" name="tipo" value="<?php echo limpiar($item['tipo']); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                <button type="submit">+</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="accion" value="restar">
                                <input type="hidden" name="tipo" value="<?php echo limpiar($item['tipo']); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                <button type="submit">-</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="tipo" value="<?php echo limpiar($item['tipo']); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                <button type="submit">x</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="acciones-carrito">
            <form method="post">
                <input type="hidden" name="accion" value="vaciar">
                <button type="submit">Vaciar carrito</button>
            </form>

            <?php if (estaLogeado()) : ?>
                <form method="post" action="comprar.php">
                    <button type="submit">Comprar</button>
                </form>
            <?php else : ?>
                <a href="login.php" class="boton-login">Login para comprar</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php require 'footer.php'; ?>
