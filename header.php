<?php
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Zombies</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="barra-superior">
    <a href="index.php" class="logo">Tienda Chombis</a>
    <nav class="menu">
        <a href="index.php">Inicio</a>
        <a href="mis_compras.php">Mis compras</a>
        <?php if (estaLogeado() && esAdmin()) : ?>
            <a href="admin_productos.php">Administrar</a>
        <?php endif; ?>
        <?php if (estaLogeado()) : ?>
            <span class="saludo">Hola, <?php echo limpiar($_SESSION['nombreUsuario']); ?></span>
            <a href="logout.php">Salir</a>
        <?php else : ?>
            <a href="login.php">Login</a>
            <a href="registro.php">Registro</a>
        <?php endif; ?>
    </nav>
</header>

<button id="botonCarrito" class="boton-carrito">Carrito (<?php echo totalItemsCarrito(); ?>)</button>

<aside id="panelCarrito" class="panel-carrito">
    <div class="panel-cabecera">
        <h2>Tu carrito</h2>
        <button id="cerrarCarrito" class="boton-cerrar">x</button>
    </div>

    <?php if (empty($carrito)) : ?>
        <p class="texto-suave">Carrito vacío</p>
    <?php else : ?>
        <ul class="lista-carrito">
            <?php foreach ($carrito as $item) : ?>
                <?php
                $nombreItem = $item['tipo'] === 'arma'
                    ? ($mapaArmas[$item['id']] ?? 'arma desconocida')
                    : ($mapaBebidas[$item['id']] ?? 'bebida desconocida');
                ?>
                <li class="item-carrito">
                    <div>
                        <strong><?php echo limpiar($nombreItem); ?></strong>
                        <span class="etiqueta"><?php echo limpiar($item['tipo']); ?></span>
                        <div class="cantidad">Cantidad: <?php echo (int) $item['cantidad']; ?></div>
                    </div>
                    <div class="acciones-item">
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
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="panel-total">Total ítems: <?php echo totalItemsCarrito(); ?></div>

        <form method="post" class="form-vaciar">
            <input type="hidden" name="accion" value="vaciar">
            <button type="submit">Vaciar carrito</button>
        </form>

        <?php if (estaLogeado()) : ?>
            <form method="post" action="comprar.php" class="form-comprar">
                <button type="submit">Comprar</button>
            </form>
        <?php else : ?>
            <a href="login.php" class="boton-login">Login para comprar</a>
        <?php endif; ?>
    <?php endif; ?>
</aside>

<main class="contenedor">
