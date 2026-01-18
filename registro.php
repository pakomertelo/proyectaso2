<?php
require 'conexion.php';
require 'funciones.php';

procesarAccionesCarrito();

$errores = [];
$nombreUsuario = '';
$email = '';
$mensajeOk = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreUsuario = trim($_POST['nombreUsuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if ($nombreUsuario === '' || strlen($nombreUsuario) < 3) {
        $errores[] = 'el nombre de usuario es obligatorio y debe tener al menos 3 caracteres';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'el email no es valido';
    }

    if ($contrasena === '' || strlen($contrasena) < 4) {
        $errores[] = 'la contraseña debe tener al menos 4 caracteres';
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare('SELECT id_usuario FROM Usuarios WHERE nombreUsuario = ? OR email = ?');
        $stmt->execute([$nombreUsuario, $email]);
        $existe = $stmt->fetch();

        if ($existe) {
            $errores[] = 'ya existe un usuario con esos datos';
        } else {
            $stmt = $pdo->prepare('INSERT INTO Usuarios (nombreUsuario, email, contrasena) VALUES (?, ?, ?)');
            $stmt->execute([$nombreUsuario, $email, $contrasena]);
            $nombreUsuario = '';
            $email = '';
            $mensajeOk = 'registro completado, ya puedes hacer login';
        }
    }
}

require 'header.php';
?>

<section class="seccion">
    <h1>registro</h1>

    <?php if (!empty($errores)) : ?>
        <div class="mensaje error">
            <?php foreach ($errores as $error) : ?>
                <p><?php echo limpiar($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensajeOk)) : ?>
        <div class="mensaje ok">
            <p><?php echo limpiar($mensajeOk); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" class="formulario">
        <label>nombre de usuario</label>
        <input type="text" name="nombreUsuario" value="<?php echo limpiar($nombreUsuario); ?>" required>

        <label>email</label>
        <input type="email" name="email" value="<?php echo limpiar($email); ?>" required>

        <label>contraseña</label>
        <input type="password" name="contrasena" required>

        <button type="submit">registrarme</button>
    </form>
</section>

<?php require 'footer.php'; ?>
