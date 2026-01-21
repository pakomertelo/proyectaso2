<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function limpiar($valor)
{
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

function estaLogeado()
{
    return isset($_SESSION['idUsuario']);
}

function esAdmin()
{
    return isset($_SESSION['nombreUsuario']) && $_SESSION['nombreUsuario'] === 'admin';
}

function redirigir($ruta)
{
    header("Location: $ruta");
    exit;
}

function obtenerCarrito()
{
    // inicializa el carrito si no existe
    if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    return $_SESSION['carrito'];
}

function guardarCarrito($carrito)
{
    $_SESSION['carrito'] = $carrito;
}

function agregarItemCarrito($tipo, $id, $cantidad = 1)
{
    // agrega o suma cantidad en el carrito
    $carrito = obtenerCarrito();

    foreach ($carrito as &$item) {
        if ($item['tipo'] === $tipo && (int) $item['id'] === (int) $id) {
            $item['cantidad'] += $cantidad;
            guardarCarrito($carrito);
            return;
        }
    }

    $carrito[] = [
        'tipo' => $tipo,
        'id' => (int) $id,
        'cantidad' => $cantidad
    ];

    guardarCarrito($carrito);
}

function sumarItemCarrito($tipo, $id)
{
    $carrito = obtenerCarrito();

    foreach ($carrito as &$item) {
        if ($item['tipo'] === $tipo && (int) $item['id'] === (int) $id) {
            $item['cantidad']++;
            guardarCarrito($carrito);
            return;
        }
    }
}

function restarItemCarrito($tipo, $id)
{
    $carrito = obtenerCarrito();

    foreach ($carrito as $index => &$item) {
        if ($item['tipo'] === $tipo && (int) $item['id'] === (int) $id) {
            $item['cantidad']--;
            if ($item['cantidad'] <= 0) {
                unset($carrito[$index]);
            }
            guardarCarrito(array_values($carrito));
            return;
        }
    }
}

function eliminarItemCarrito($tipo, $id)
{
    $carrito = obtenerCarrito();

    foreach ($carrito as $index => $item) {
        if ($item['tipo'] === $tipo && (int) $item['id'] === (int) $id) {
            unset($carrito[$index]);
            guardarCarrito(array_values($carrito));
            return;
        }
    }
}

function vaciarCarrito()
{
    $_SESSION['carrito'] = [];
}

function totalItemsCarrito()
{
    $carrito = obtenerCarrito();
    $total = 0;

    foreach ($carrito as $item) {
        $total += (int) $item['cantidad'];
    }

    return $total;
}

function procesarAccionesCarrito()
{
    // procesa acciones simples del carrito
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['accion'])) {
        return;
    }

    $accion = $_POST['accion'];
    $tipo = $_POST['tipo'] ?? '';
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($accion === 'agregar' && $tipo && $id > 0) {
        agregarItemCarrito($tipo, $id, 1);
    }

    if ($accion === 'sumar' && $tipo && $id > 0) {
        sumarItemCarrito($tipo, $id);
    }

    if ($accion === 'restar' && $tipo && $id > 0) {
        restarItemCarrito($tipo, $id);
    }

    if ($accion === 'eliminar' && $tipo && $id > 0) {
        eliminarItemCarrito($tipo, $id);
    }

    if ($accion === 'vaciar') {
        vaciarCarrito();
    }
}
