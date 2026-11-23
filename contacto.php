<?php
header('Content-Type: application/json; charset=utf-8');

// ===== BASE DE DATOS =====
// XAMPP (tu computadora):
$servidor = 'localhost';
$base     = 'portafolio';
$usuario  = 'root';
$clave    = '';
// En InfinityFree cambia estas 4 líneas por los datos que te da el panel.

// Correo donde quieres recibir los mensajes
$destino   = 'betsy.flores@gmail.com';
$remitente = 'contacto@betsyflores.com';

function responder($ok, $error = '') {
  echo json_encode(['ok' => $ok, 'error' => $error]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responder(false, 'Método no permitido.');

// Campo trampa para bots
if (!empty($_POST['web'])) responder(true);

$nombre  = trim(strip_tags($_POST['nombre']  ?? ''));
$correo  = trim($_POST['correo']  ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$mensaje = trim(strip_tags($_POST['mensaje'] ?? ''));

if ($nombre === '' || $telefono === '' || $mensaje === '') responder(false, 'Completa todos los campos.');
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) responder(false, 'El correo no es válido.');
if (!preg_match('/^[0-9+() -]{6,15}$/', $telefono)) responder(false, 'El teléfono no es válido.');
if (mb_strlen($nombre) > 80 || mb_strlen($mensaje) > 1500) responder(false, 'El texto es demasiado largo.');

$nombre = str_replace(["\r", "\n"], ' ', $nombre);

// Guardar en la base de datos
mysqli_report(MYSQLI_REPORT_OFF);
$cn = @new mysqli($servidor, $usuario, $clave, $base);
if ($cn->connect_errno) {
  $detalle = ($servidor === 'localhost') ? ' (' . $cn->connect_error . ')' : '';
  responder(false, 'No se pudo conectar con la base de datos.' . $detalle);
}
$cn->set_charset('utf8mb4');
$st = $cn->prepare('INSERT INTO mensajes(nombre,correo,telefono,mensaje) VALUES(?,?,?,?)');
if (!$st) {
  $detalle = ($servidor === 'localhost') ? ' (' . $cn->error . ')' : '';
  responder(false, 'No se pudo guardar el mensaje.' . $detalle);
}
$st->bind_param('ssss', $nombre, $correo, $telefono, $mensaje);
if (!$st->execute()) {
  $detalle = ($servidor === 'localhost') ? ' (' . $st->error . ')' : '';
  responder(false, 'No se pudo guardar el mensaje.' . $detalle);
}
$st->close();
$cn->close();

// Aviso por correo (en XAMPP normalmente no sale; en InfinityFree sí)
$asunto = 'Nuevo mensaje del portafolio de ' . $nombre;
$cuerpo = "Nombre: $nombre\nCorreo: $correo\nTeléfono: $telefono\n\nMensaje:\n$mensaje\n";
$cabeceras = "From: Portafolio <$remitente>\r\nReply-To: $correo\r\nContent-Type: text/plain; charset=UTF-8\r\n";
@mail($destino, '=?UTF-8?B?' . base64_encode($asunto) . '?=', $cuerpo, $cabeceras);

responder(true);
