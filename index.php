<?php
require 'conexion.php';
require 'funciones.php';

procesarAccionesCarrito();

$listaArmas = $pdo->query('SELECT * FROM Armas ORDER BY nombre')->fetchAll();
$listaBebidas = $pdo->query('SELECT * FROM Bebidas ORDER BY nombre')->fetchAll();

require 'header.php';
?>

<section class="seccion">
    <h1>armas</h1>
    <div class="grid">
        <?php foreach ($listaArmas as $arma) : ?>
            <article class="card">
                <h3><?php echo limpiar($arma['nombre']); ?></h3>
                <p>clase: <?php echo limpiar($arma['clase'] ?? ''); ?></p>
                <p>pap: <?php echo limpiar($arma['nombre_pap'] ?? ''); ?></p>
                <p><?php echo $arma['es_wonder_weapon'] ? 'wonder weapon' : 'arma normal'; ?></p>
                <form method="post">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="tipo" value="arma">
                    <input type="hidden" name="id" value="<?php echo (int) $arma['id_arma']; ?>">
                    <button type="submit">añadir</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="seccion">
    <h1>perks</h1>
    <div class="grid">
        <?php foreach ($listaBebidas as $bebida) : ?>
            <article class="card">
                <h3><?php echo limpiar($bebida['nombre']); ?></h3>
                <p>coste: <?php echo (int) $bebida['coste']; ?></p>
                <p><?php echo limpiar($bebida['efecto'] ?? ''); ?></p>
                <form method="post">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="tipo" value="bebida">
                    <input type="hidden" name="id" value="<?php echo (int) $bebida['id_bebida']; ?>">
                    <button type="submit">añadir</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require 'footer.php'; ?>
