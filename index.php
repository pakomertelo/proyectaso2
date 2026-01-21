<?php
require 'conexion.php';
require 'funciones.php';

$ordenArmas = $_GET['orden_armas'] ?? 'nombre_asc';

$ordenesArmasPermitidos = [
    'nombre_asc' => 'nombre ASC',
    'nombre_desc'=> 'nombre DESC',
    'clase_asc'  => 'clase ASC',
    'clase_desc' => 'clase DESC',
    'coste_asc'  => 'coste ASC',
    'coste_desc' => 'coste DESC',
    'pap_asc'    => 'nombre_pap IS NULL ASC',
    'pap_desc'   => 'nombre_pap IS NULL DESC'
];

$orderByArmas = $ordenesArmasPermitidos[$ordenArmas] ?? 'nombre ASC';

$ordenBebidas = $_GET['orden_bebidas'] ?? 'nombre_asc';

$ordenesBebidasPermitidos = [
    'nombre_asc' => 'nombre ASC',
    'nombre_desc'=> 'nombre DESC',
    'coste_asc'  => 'coste ASC',
    'coste_desc' => 'coste DESC'
];

$orderByBebidas = $ordenesBebidasPermitidos[$ordenBebidas] ?? 'nombre ASC';

procesarAccionesCarrito();

$listaArmas = $pdo->query('SELECT * FROM Armas ORDER BY ' . $orderByArmas)->fetchAll();
$listaBebidas = $pdo->query('SELECT * FROM Bebidas ORDER BY ' . $orderByBebidas)->fetchAll();

require 'header.php';
?>

<form method="get" class="orden-form">
    <label for="orden_armas">Ordenar armas por:</label>
    <select name="orden_armas" id="orden_armas" onchange="this.form.submit()">
        <option value="nombre_asc">Nombre (A-Z)</option>
        <option value="nombre_desc">Nombre (Z-A)</option>
        <option value="clase_asc">Clase (A-Z)</option>
        <option value="clase_desc">Clase (Z-A)</option>
        <option value="coste_asc">Coste (↑)</option>
        <option value="coste_desc">Coste (↓)</option>
        <option value="pap_asc">Con PaP primero</option>
        <option value="pap_desc">Sin PaP primero</option>
    </select>
</form>

<section class="seccion">
    <h1>Armas</h1>
    <div class="grid">
        <?php foreach ($listaArmas as $arma) : ?>
            <article class="card">
                <h3><?php echo limpiar($arma['nombre']); ?></h3>
                <p>Clase: <?php echo limpiar($arma['clase'] ?? ''); ?></p>
                <p>PaP: <?php echo limpiar($arma['nombre_pap'] ?? ''); ?></p>
                <p>Coste: <?php echo (int) $arma['coste']; ?></p>

                <p><?php echo $arma['es_wonder_weapon'] ? 'Wonder Weapon' : 'Arma normal'; ?></p>
                <form method="post">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="tipo" value="arma">
                    <input type="hidden" name="id" value="<?php echo (int) $arma['id_arma']; ?>">
                    <img src="<?php echo limpiar($arma['path']); ?>" alt="<?php echo limpiar($arma['nombre']); ?>">
                    <button type="submit">Añadir</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<form method="get" class="orden-form">
    <label for="orden_bebidas">Ordenar perks por:</label>
    <select name="orden_bebidas" id="orden_bebidas" onchange="this.form.submit()">
        <option value="nombre_asc">Nombre (A-Z)</option>
        <option value="nombre_desc">Nombre (Z-A)</option>
        <option value="coste_asc">Coste (↑)</option>
        <option value="coste_desc">Coste (↓)</option>
    </select>
</form>

<section class="seccion">
    <h1>Perks</h1>
    <div class="grid">
        <?php foreach ($listaBebidas as $bebida) : ?>
            <article class="card">
                <h3><?php echo limpiar($bebida['nombre']); ?></h3>
                <p>Coste: <?php echo (int) $bebida['coste']; ?></p>
                <p><?php echo limpiar($bebida['efecto'] ?? ''); ?></p>
                <form method="post">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="tipo" value="bebida">
                    <input type="hidden" name="id" value="<?php echo (int) $bebida['id_bebida']; ?>">
                    <img src="<?php echo limpiar($bebida['path']); ?>" alt="<?php echo limpiar($bebida['nombre']); ?>">
                    <button type="submit">Añadir</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<button id="btnTop" title="Subir">↑</button>

<?php require 'footer.php'; ?>

<script>
const btnTop = document.getElementById("btnTop");

window.addEventListener("scroll", () => {
    btnTop.style.display = window.scrollY > 300 ? "block" : "none";
});

btnTop.addEventListener("click", () => {
    window.scrollTo({
    top: 0,
    behavior: "smooth"
    });
});
</script>

