<?php
/**
 * Procesa el formulario de registro de clientes
 * IACC - Programación Web II - Semana 6
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/database.php';

// Solo procesar si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: clientes_form.php');
    exit;
}

// Recoger y limpiar los datos del formulario
$nombre    = trim($_POST['nombre'] ?? '');
$email     = trim($_POST['email'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

$errores = [];

// --- Validaciones en el servidor (nunca confiar solo en JavaScript) ---

if (mb_strlen($nombre) < 5) {
    $errores[] = "El nombre debe tener al menos 5 caracteres.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El correo electrónico no es válido.";
}

if (empty($direccion)) {
    $errores[] = "La dirección es obligatoria.";
}

// El teléfono es opcional, pero si viene, validamos formato básico
if (!empty($telefono) && !preg_match('/^[\+]?[0-9\s\-\(\)]{8,20}$/', $telefono)) {
    $errores[] = "El teléfono no tiene un formato válido.";
}

// Si hay errores, volvemos al formulario mostrando el problema
if (!empty($errores)) {
    echo "<h2>No se pudo guardar el cliente</h2><ul>";
    foreach ($errores as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo '<a href="clientes_form.php">← Volver al formulario</a>';
    exit;
}

// --- Insertar en la base de datos ---

$db = new Database();
$conexion = $db->getConnection();

// Evitar duplicar el mismo correo
$stmt = $conexion->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$existente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existente) {
    echo "<h2>Este correo ya está registrado</h2>";
    echo '<a href="clientes_form.php">← Volver al formulario</a>';
    exit;
}

$stmt = $conexion->prepare(
    "INSERT INTO cliente (nombre, email, direccion, telefono) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $nombre, $email, $direccion, $telefono);

if ($stmt->execute()) {
    echo "<h2>✅ Cliente registrado correctamente</h2>";
    echo "<p>" . htmlspecialchars($nombre) . " fue agregado con el ID " . $conexion->insert_id . "</p>";
    echo '<a href="clientes_form.php">➕ Registrar otro cliente</a> | ';
    echo '<a href="listar_clientes.php">📋 Ver todos los clientes</a>';
} else {
    echo "<h2>❌ Error al guardar</h2>";
    echo "<p>" . htmlspecialchars($conexion->error) . "</p>";
}

$stmt->close();
$db->close();
?>