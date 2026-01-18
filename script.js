const botonCarrito = document.getElementById('botonCarrito');
const panelCarrito = document.getElementById('panelCarrito');
const cerrarCarrito = document.getElementById('cerrarCarrito');

if (botonCarrito && panelCarrito) {
    botonCarrito.addEventListener('click', () => {
        panelCarrito.classList.add('abierto');
    });
}

if (cerrarCarrito && panelCarrito) {
    cerrarCarrito.addEventListener('click', () => {
        panelCarrito.classList.remove('abierto');
    });
}
