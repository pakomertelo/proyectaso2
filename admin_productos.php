<?php
require 'conexion.php';
require 'funciones.php';

procesarAccionesCarrito();

if (!estaLogeado() || !esAdmin()) {
    redirigir('index.php');
}

$mensaje = $_GET['mensaje'] ?? '';
$accion = $_GET['accion'] ?? 'listar';
$tipo = $_GET['tipo'] ?? '';
$editId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$errores = [];
$extensionesPermitidas = ['png', 'jpg', 'webp'];

function normalizarNombreArchivo(string $nombreOriginal): string
{
    $base = pathinfo($nombreOriginal, PATHINFO_FILENAME);
    $base = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $base);
    $base = trim($base, '_-');

    return $base ?? '';
}

function esRutaSegura(string $ruta, string $directorioPermitido): bool
{
    if (!str_starts_with($ruta, './')) {
        return false;
    }

    $rutaCompleta = realpath(__DIR__ . '/' . ltrim($ruta, './'));
    $directorioCompleto = realpath($directorioPermitido);

    if (!$rutaCompleta || !$directorioCompleto) {
        return false;
    }

    return str_starts_with($rutaCompleta, $directorioCompleto . DIRECTORY_SEPARATOR);
}

$datos = [
    'nombre' => '',
    'coste' => '',
    'path' => '',
    'clase' => '',
    'nombre_pap' => '',
    'es_wonder_weapon' => 0,
    'efecto' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accionPost = $_POST['accion'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $editId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $registroActual = null;
    $rutaAnterior = '';
    $imagenSubida = isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE;

    $datos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'coste' => trim($_POST['coste'] ?? ''),
        'path' => '',
        'clase' => trim($_POST['clase'] ?? ''),
        'nombre_pap' => trim($_POST['nombre_pap'] ?? ''),
        'es_wonder_weapon' => isset($_POST['es_wonder_weapon']) ? 1 : 0,
        'efecto' => trim($_POST['efecto'] ?? '')
    ];

    if ($accionPost === 'eliminar') {
        if ($editId <= 0 || !in_array($tipo, ['arma', 'bebida'], true)) {
            $errores[] = 'Producto inválido para eliminar';
        } else {
            if ($tipo === 'arma') {
                $stmt = $pdo->prepare('DELETE FROM Armas WHERE id_arma = ?');
                $stmt->execute([$editId]);
            } else {
                $stmt = $pdo->prepare('DELETE FROM Bebidas WHERE id_bebida = ?');
                $stmt->execute([$editId]);
            }

            redirigir('admin_productos.php?mensaje=eliminado');
        }
    }

    if (in_array($accionPost, ['crear', 'editar'], true)) {
        if (!in_array($tipo, ['arma', 'bebida'], true)) {
            $errores[] = 'Selecciona un tipo válido';
        }

        if ($datos['nombre'] === '') {
            $errores[] = 'Falta nombre';
        }

        if ($datos['coste'] === '') {
            $errores[] = 'Falta coste';
        } elseif (!is_numeric($datos['coste'])) {
            $errores[] = 'El coste debe ser un número válido';
        } elseif ((float) $datos['coste'] < 0) {
            $errores[] = 'El coste no puede ser negativo';
        }

        if ($accionPost === 'crear') {
            if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
                $errores[] = 'Falta imagen';
            } elseif ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                $errores[] = 'Error al subir la imagen';
            }
        } elseif ($imagenSubida && $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'Error al subir la imagen';
        }

        if ($tipo === 'arma' && $datos['clase'] === '') {
            $errores[] = 'Falta clase';
        }

        if ($tipo === 'bebida' && $datos['efecto'] === '') {
            $errores[] = 'Falta efecto';
        }

        if ($accionPost === 'editar' && $editId <= 0) {
            $errores[] = 'Producto inválido para editar';
        }

        if ($accionPost === 'editar' && $editId > 0 && in_array($tipo, ['arma', 'bebida'], true)) {
            if ($tipo === 'arma') {
                $stmt = $pdo->prepare('SELECT path FROM Armas WHERE id_arma = ?');
            } else {
                $stmt = $pdo->prepare('SELECT path FROM Bebidas WHERE id_bebida = ?');
            }
            $stmt->execute([$editId]);
            $registroActual = $stmt->fetch();

            if (!$registroActual) {
                $errores[] = 'Producto inválido para editar';
            } else {
                $rutaAnterior = $registroActual['path'] ?? '';
                $datos['path'] = $rutaAnterior;
            }
        }

        if ($datos['nombre'] !== '' && in_array($tipo, ['arma', 'bebida'], true)) {
            if ($tipo === 'arma') {
                $query = 'SELECT COUNT(*) FROM Armas WHERE nombre = ?';
                $params = [$datos['nombre']];
                if ($accionPost === 'editar') {
                    $query .= ' AND id_arma != ?';
                    $params[] = $editId;
                }
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                if ((int) $stmt->fetchColumn() > 0) {
                    $errores[] = 'Ya existe un arma con ese nombre';
                }
            } else {
                $query = 'SELECT COUNT(*) FROM Bebidas WHERE nombre = ?';
                $params = [$datos['nombre']];
                if ($accionPost === 'editar') {
                    $query .= ' AND id_bebida != ?';
                    $params[] = $editId;
                }
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                if ((int) $stmt->fetchColumn() > 0) {
                    $errores[] = 'Ya existe una bebida con ese nombre';
                }
            }
        }

        if ($imagenSubida) {
            $nombreOriginal = $_FILES['imagen']['name'] ?? '';
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $nombreLimpio = normalizarNombreArchivo($nombreOriginal);

            if ($nombreLimpio === '') {
                $errores[] = 'Nombre de archivo inválido';
            }

            if (!in_array($extension, $extensionesPermitidas, true)) {
                $errores[] = 'Formato de imagen no permitido (png, jpg, webp)';
            }
        }

        if (empty($errores)) {
            $directorioBase = $tipo === 'arma' ? __DIR__ . '/weapons' : __DIR__ . '/perks';
            $prefijoRuta = $tipo === 'arma' ? './weapons/' : './perks/';

            if ($imagenSubida) {
                if (!is_dir($directorioBase)) {
                    if (!mkdir($directorioBase, 0755, true) && !is_dir($directorioBase)) {
                        $errores[] = 'No se pudo crear la carpeta de imágenes';
                    }
                }

                if (empty($errores)) {
                    $nombreOriginal = $_FILES['imagen']['name'] ?? '';
                    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                    $nombreLimpio = normalizarNombreArchivo($nombreOriginal);
                    $nombreArchivo = $nombreLimpio . '.' . $extension;
                    $rutaDestino = $directorioBase . '/' . $nombreArchivo;
                    $rutaRelativa = $prefijoRuta . $nombreArchivo;

                    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                        $errores[] = 'No se pudo guardar la imagen';
                    } else {
                        $datos['path'] = $rutaRelativa;
                    }
                }
            } else {
                $datos['path'] = $rutaAnterior;
            }
        }

        if (empty($errores)) {
            if ($tipo === 'arma') {
                $nombrePap = $datos['nombre_pap'] !== '' ? $datos['nombre_pap'] : null;

                if ($accionPost === 'crear') {
                    $stmt = $pdo->prepare('INSERT INTO Armas (nombre, clase, coste, nombre_pap, es_wonder_weapon, path) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $datos['nombre'],
                        $datos['clase'],
                        $datos['coste'],
                        $nombrePap,
                        $datos['es_wonder_weapon'],
                        $datos['path']
                    ]);

                    redirigir('admin_productos.php?mensaje=creado');
                } else {
                    $stmt = $pdo->prepare('UPDATE Armas SET nombre = ?, clase = ?, coste = ?, nombre_pap = ?, es_wonder_weapon = ?, path = ? WHERE id_arma = ?');
                    $stmt->execute([
                        $datos['nombre'],
                        $datos['clase'],
                        $datos['coste'],
                        $nombrePap,
                        $datos['es_wonder_weapon'],
                        $datos['path'],
                        $editId
                    ]);

                    $rutaAnteriorCompleta = __DIR__ . '/' . ltrim($rutaAnterior, './');
                    if ($imagenSubida && $rutaAnterior !== '' && esRutaSegura($rutaAnterior, __DIR__ . '/weapons') && is_file($rutaAnteriorCompleta)) {
                        unlink($rutaAnteriorCompleta);
                    }

                    redirigir('admin_productos.php?mensaje=actualizado');
                }
            }

            if ($tipo === 'bebida') {
                if ($accionPost === 'crear') {
                    $stmt = $pdo->prepare('INSERT INTO Bebidas (nombre, coste, efecto, path) VALUES (?, ?, ?, ?)');
                    $stmt->execute([
                        $datos['nombre'],
                        $datos['coste'],
                        $datos['efecto'],
                        $datos['path']
                    ]);

                    redirigir('admin_productos.php?mensaje=creado');
                } else {
                    $stmt = $pdo->prepare('UPDATE Bebidas SET nombre = ?, coste = ?, efecto = ?, path = ? WHERE id_bebida = ?');
                    $stmt->execute([
                        $datos['nombre'],
                        $datos['coste'],
                        $datos['efecto'],
                        $datos['path'],
                        $editId
                    ]);

                    $rutaAnteriorCompleta = __DIR__ . '/' . ltrim($rutaAnterior, './');
                    if ($imagenSubida && $rutaAnterior !== '' && esRutaSegura($rutaAnterior, __DIR__ . '/perks') && is_file($rutaAnteriorCompleta)) {
                        unlink($rutaAnteriorCompleta);
                    }

                    redirigir('admin_productos.php?mensaje=actualizado');
                }
            }
        } else {
            $accion = $accionPost;
        }
    }
}

if ($accion === 'editar' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!in_array($tipo, ['arma', 'bebida'], true) || $editId <= 0) {
        $errores[] = 'Producto inválido para editar';
        $accion = 'listar';
    } else {
        if ($tipo === 'arma') {
            $stmt = $pdo->prepare('SELECT * FROM Armas WHERE id_arma = ?');
            $stmt->execute([$editId]);
            $arma = $stmt->fetch();

            if ($arma) {
                $datos = [
                    'nombre' => $arma['nombre'],
                    'coste' => $arma['coste'],
                    'path' => $arma['path'],
                    'clase' => $arma['clase'],
                    'nombre_pap' => $arma['nombre_pap'] ?? '',
                    'es_wonder_weapon' => (int) $arma['es_wonder_weapon'],
                    'efecto' => ''
                ];
            } else {
                $errores[] = 'Producto no encontrado';
                $accion = 'listar';
            }
        }

        if ($tipo === 'bebida') {
            $stmt = $pdo->prepare('SELECT * FROM Bebidas WHERE id_bebida = ?');
            $stmt->execute([$editId]);
            $bebida = $stmt->fetch();

            if ($bebida) {
                $datos = [
                    'nombre' => $bebida['nombre'],
                    'coste' => $bebida['coste'],
                    'path' => $bebida['path'],
                    'clase' => '',
                    'nombre_pap' => '',
                    'es_wonder_weapon' => 0,
                    'efecto' => $bebida['efecto'] ?? ''
                ];
            } else {
                $errores[] = 'Producto no encontrado';
                $accion = 'listar';
            }
        }
    }
}

$armas = $pdo->query('SELECT id_arma, nombre, clase, coste, nombre_pap, es_wonder_weapon, path FROM Armas ORDER BY nombre ASC')->fetchAll();
$bebidas = $pdo->query('SELECT id_bebida, nombre, coste, efecto, path FROM Bebidas ORDER BY nombre ASC')->fetchAll();

$productos = [];

foreach ($armas as $arma) {
    $detalle = 'Clase: ' . ($arma['clase'] ?? '') . ' | PaP: ' . ($arma['nombre_pap'] ?? '');
    $detalle .= $arma['es_wonder_weapon'] ? ' | Wonder' : '';

    $productos[] = [
        'id' => $arma['id_arma'],
        'nombre' => $arma['nombre'],
        'tipo' => 'arma',
        'detalle' => $detalle,
        'coste' => $arma['coste'],
        'path' => $arma['path']
    ];
}

foreach ($bebidas as $bebida) {
    $productos[] = [
        'id' => $bebida['id_bebida'],
        'nombre' => $bebida['nombre'],
        'tipo' => 'bebida',
        'detalle' => 'Efecto: ' . ($bebida['efecto'] ?? ''),
        'coste' => $bebida['coste'],
        'path' => $bebida['path']
    ];
}

require 'header.php';
?>

<section class="seccion">
    <h1>Administración de productos</h1>

    <?php if ($mensaje) : ?>
        <div class="mensaje ok">
            <p>
                <?php if ($mensaje === 'creado') : ?>Producto creado correctamente.
                <?php elseif ($mensaje === 'actualizado') : ?>Producto actualizado correctamente.
                <?php elseif ($mensaje === 'eliminado') : ?>Producto eliminado correctamente.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)) : ?>
        <div class="mensaje error">
            <?php foreach ($errores as $error) : ?>
                <p><?php echo limpiar($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="admin_productos.php?accion=crear" class="boton-secundario">Añadir producto</a>
</section>

<?php if ($accion === 'crear' && !in_array($tipo, ['arma', 'bebida'], true)) : ?>
    <section class="seccion">
        <h2>Selecciona el tipo de producto</h2>
        <div class="acciones-carrito">
            <a class="boton-secundario" href="admin_productos.php?accion=crear&amp;tipo=arma">Arma</a>
            <a class="boton-secundario" href="admin_productos.php?accion=crear&amp;tipo=bebida">Bebida</a>
        </div>
    </section>
<?php endif; ?>

<?php if (in_array($accion, ['crear', 'editar'], true) && in_array($tipo, ['arma', 'bebida'], true)) : ?>
    <section class="seccion">
        <h2><?php echo $accion === 'crear' ? 'Añadir' : 'Editar'; ?> <?php echo $tipo === 'arma' ? 'arma' : 'bebida'; ?></h2>

        <form method="post" class="formulario" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="<?php echo $accion; ?>">
            <input type="hidden" name="tipo" value="<?php echo limpiar($tipo); ?>">
            <?php if ($accion === 'editar') : ?>
                <input type="hidden" name="id" value="<?php echo (int) $editId; ?>">
            <?php endif; ?>

            <label>Nombre</label>
            <input type="text" name="nombre" value="<?php echo limpiar($datos['nombre']); ?>">

            <label>Coste</label>
            <input type="number" name="coste" min="0" step="0.01" value="<?php echo limpiar($datos['coste']); ?>">

            <label>Imagen</label>
            <input type="file" name="imagen" accept=".png,.jpg,.webp">
            <?php if ($accion === 'editar' && $datos['path'] !== '') : ?>
                <p class="texto-suave">Actual: <?php echo limpiar($datos['path']); ?></p>
            <?php endif; ?>

            <?php if ($tipo === 'arma') : ?>
                <label>Clase</label>
                <input type="text" name="clase" value="<?php echo limpiar($datos['clase']); ?>">

                <label>Nombre PaP</label>
                <input type="text" name="nombre_pap" value="<?php echo limpiar($datos['nombre_pap']); ?>">

                <label>
                    <input type="checkbox" name="es_wonder_weapon" value="1" <?php echo $datos['es_wonder_weapon'] ? 'checked' : ''; ?>>
                    Wonder Weapon
                </label>
            <?php endif; ?>

            <?php if ($tipo === 'bebida') : ?>
                <label>Efecto</label>
                <input type="text" name="efecto" value="<?php echo limpiar($datos['efecto']); ?>">
            <?php endif; ?>

            <button type="submit"><?php echo $accion === 'crear' ? 'Crear' : 'Actualizar'; ?></button>
        </form>
    </section>
<?php endif; ?>

<section class="seccion">
    <h2>Listado de productos</h2>

    <?php if (empty($productos)) : ?>
        <p class="texto-suave">No hay productos registrados.</p>
    <?php else : ?>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Detalle</th>
                    <th>Coste</th>
                    <th>Path</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto) : ?>
                    <tr>
                        <td><?php echo limpiar($producto['nombre']); ?></td>
                        <td><?php echo limpiar($producto['tipo']); ?></td>
                        <td><?php echo limpiar($producto['detalle']); ?></td>
                        <td><?php echo (int) $producto['coste']; ?></td>
                        <td><?php echo limpiar($producto['path']); ?></td>
                        <td>
                            <div class="fila-acciones">
                                <a class="boton-secundario" href="admin_productos.php?accion=editar&amp;tipo=<?php echo limpiar($producto['tipo']); ?>&amp;id=<?php echo (int) $producto['id']; ?>">Editar</a>
                                <form method="post" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="tipo" value="<?php echo limpiar($producto['tipo']); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $producto['id']; ?>">
                                    <button type="submit" class="boton-secundario">Borrar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require 'footer.php'; ?>
