<?php
/**
 * Sistema de seguimiento de pedidos
 * CORRECCIÓN: Lee pedidos desde archivo JSON en lugar de array vacío
 */

session_start();

/**
 * Función para cargar pedidos desde archivo JSON
 */
function cargarPedidosDesdeArchivo() {
    $archivo = 'pedidos.json';
    
    if (!file_exists($archivo)) {
        return [];
    }
    
    $jsonContent = file_get_contents($archivo);
    $pedidos = json_decode($jsonContent, true);
    
    return $pedidos ?: [];
}

/**
 * Función para buscar pedido por ID
 */
function buscarPedido($idPedido, $pedidos) {
    foreach ($pedidos as $pedido) {
        if ($pedido['id'] == $idPedido) {
            return $pedido;
        }
    }
    return null;
}

// Procesar búsqueda si se envió el formulario
$pedidoEncontrado = null;
$error = "";
$pedidosDB = cargarPedidosDesdeArchivo(); // Cargar pedidos reales

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_seguimiento'])) {
    $idBuscado = htmlspecialchars(trim($_POST['id_seguimiento']));
    
    if (empty($idBuscado)) {
        $error = "Por favor ingresa un ID de pedido";
    } else {
        $pedidoEncontrado = buscarPedido($idBuscado, $pedidosDB);
        
        if (!$pedidoEncontrado) {
            $error = "No se encontró un pedido con el ID: " . $idBuscado;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Seguimiento de Pedidos - Tienda Online</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .search-form {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .search-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }
        .search-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .search-form button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .search-form button:hover {
            background-color: #0056b3;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            margin-bottom: 20px;
        }
        .pedido-detalle {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #545b62;
        }
        .estado-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .estado-pendiente { background-color: #ffc107; color: #000; }
        .estado-procesando { background-color: #17a2b8; color: white; }
        .estado-enviado { background-color: #007bff; color: white; }
        .estado-entregado { background-color: #28a745; color: white; }
    </style>
</head>
<body>
    <header>
        <h1>🛍️ Mi Tienda Online</h1>
        <nav class="main-nav">
            <a href="index.html" class="nav-link">🏠 Inicio</a>
            <a href="formulario_pedido.html" class="nav-link">📦 Nuevo Pedido</a>
            <a href="seguimiento_pedido.php" class="nav-link active">📍 Seguimiento</a>
        </nav>
    </header>
    
    <div class="container">
        <h1>📍 Seguimiento de Pedidos</h1>
        
        <div class="search-form">
            <form method="post" action="">
                <label for="id_seguimiento">Ingresa el ID de tu pedido:</label>
                <input type="text" 
                       id="id_seguimiento" 
                       name="id_seguimiento" 
                       placeholder="Ej: PED-20240115-1234"
                       value="<?php echo isset($_POST['id_seguimiento']) ? htmlspecialchars($_POST['id_seguimiento']) : ''; ?>"
                       required>
                <button type="submit">🔍 Buscar Pedido</button>
            </form>
        </div>
        
        <?php if ($error): ?>
        <div class="error">
            ❌ <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($pedidoEncontrado): ?>
        <div class="success">
            ✅ <strong>Pedido encontrado:</strong> <?php echo $pedidoEncontrado['id']; ?>
        </div>
        
        <div class="pedido-detalle">
            <h3>📦 Detalles del Pedido</h3>
            <div class="info-row">
                <span class="info-label">ID del Pedido:</span>
                <span><?php echo $pedidoEncontrado['id']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Descripción:</span>
                <span><?php echo $pedidoEncontrado['descripcion']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tipo de Pedido:</span>
                <span><?php echo $pedidoEncontrado['tipo']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Producto:</span>
                <span><?php echo $pedidoEncontrado['producto']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Cantidad:</span>
                <span><?php echo $pedidoEncontrado['unidades']; ?> unidades</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total:</span>
                <span>$<?php echo number_format($pedidoEncontrado['total'], 0, ',', '.'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <?php 
                $estadoClass = 'estado-pendiente';
                if ($pedidoEncontrado['estado'] === 'Procesando') $estadoClass = 'estado-procesando';
                if ($pedidoEncontrado['estado'] === 'Enviado') $estadoClass = 'estado-enviado';
                if ($pedidoEncontrado['estado'] === 'Entregado') $estadoClass = 'estado-entregado';
                ?>
                <span class="estado-badge <?php echo $estadoClass; ?>">
                    <?php echo $pedidoEncontrado['estado']; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span><?php echo date('d/m/Y H:i', strtotime($pedidoEncontrado['fecha'])); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <a href="index.html" class="btn-back">← Volver a la Tienda</a>
    </div>
</body>
</html>