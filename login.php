<?php
require 'conexion.php';
require 'funciones.php';

procesarAccionesCarrito();

$errores = [];
$nombreUsuario = $_COOKIE['recordarUsuario'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreUsuario = trim($_POST['nombreUsuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if ($nombreUsuario === '' || strlen($nombreUsuario) < 3) {
        $errores[] = 'El nombre de usuario es obligatorio';
    }

    if ($contrasena === '' || strlen($contrasena) < 4) {
        $errores[] = 'La contraseña es obligatoria';
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare('SELECT id_usuario, nombreUsuario FROM Usuarios WHERE nombreUsuario = ? AND contrasena = ?');
        $stmt->execute([$nombreUsuario, $contrasena]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $_SESSION['idUsuario'] = $usuario['id_usuario'];
            $_SESSION['nombreUsuario'] = $usuario['nombreUsuario'];
            setcookie('recordarUsuario', $usuario['nombreUsuario'], time() + 60 * 60 * 24 * 7, '/');
            redirigir('index.php');
        } else {
            $errores[] = 'Datos incorrectos uwu';
        }
    }
}

require 'header.php';
?>

<section class="seccion">
    <h1>Login</h1>

    <?php if (!empty($errores)) : ?>
        <div class="mensaje error">
            <?php foreach ($errores as $error) : ?>
                <p><?php echo limpiar($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="formulario">
        <label>Nombre de usuario</label>
        <input type="text" name="nombreUsuario" value="<?php echo limpiar($nombreUsuario); ?>" required>

        <label>Contraseña</label>
        <input type="password" name="contrasena" required>

        <button type="submit">Entrar</button>
    </form>
</section>

<?php require 'footer.php'; ?>
