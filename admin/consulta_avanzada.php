<?php
/**
 * Consulta Avanzada: Clientes con más de 2 compras
 * 
 * Esta consulta utiliza:
 * - INNER JOIN para unir tablas
 * - GROUP BY para agrupar por cliente
 * - HAVING para filtrar grupos
 * - Función de agregación COUNT()
 */
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$conn = $db->getConnection();

// CONSULTA AVANZADA
$sql = "SELECT 
            c.id_cliente,
            cl.nombre AS nombre_cliente,
            cl.email,
            COUNT(c.id_compra) AS total_compras,
            SUM(c.cantidad) AS total_productos,
            SUM(c.total) AS monto_total_gastado,
            AVG(c.total) AS promedio_por_compra,
            MIN(c.fecha_compra) AS primera_compra,
            MAX(c.fecha_compra) AS ultima_compra
        FROM COMPRA c
        INNER JOIN CLIENTE cl ON c.id_cliente = cl.id_cliente
        GROUP BY c.id_cliente, cl.nombre, cl.email
        HAVING COUNT(c.id_compra) > 2
        ORDER BY total_compras DESC, monto_total_gastado DESC";

$resultado = $conn->query($sql);

// Consulta para estadísticas generales
$sql_stats = "SELECT 
                COUNT(DISTINCT id_compra) as total_transacciones,
                COUNT(DISTINCT id_cliente) as clientes_unicos,
                SUM(total) as ingresos_totales,
                AVG(total) as ticket_promedio
              FROM COMPRA";
$stats = $conn->query($sql_stats)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Avanzada - Clientes Frecuentes</title>
    <link rel="stylesheet" href="../styles_admin.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 2em;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        .consulta-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .consulta-info code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h2>📊 Consulta Avanzada: Clientes Frecuentes</h2>
        
        <div class="consulta-info">
            <h3>📝 Descripción de la Consulta</h3>
            <p><strong>Objetivo:</strong> Mostrar clientes que han realizado <strong>MÁS DE DOS COMPRAS</strong></p>
            <p><strong>Técnicas SQL utilizadas:</strong></p>
            <ul>
                <li><code>INNER JOIN</code> - Para unir las tablas COMPRA y CLIENTE</li>
                <li><code>GROUP BY</code> - Para agrupar las compras por cliente</li>
                <li><code>HAVING</code> - Para filtrar grupos con más de 2 compras</li>
                <li><code>COUNT(), SUM(), AVG(), MIN(), MAX()</code> - Funciones de agregación</li>
            </ul>
        </div>

        <!-- Estadísticas Generales -->
        <div class="stats-container">
            <div class="stat-card">
                <h3><?php echo $stats['total_transacciones']; ?></h3>
                <p>Total de Compras</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['clientes_unicos']; ?></h3>
                <p>Clientes Únicos</p>
            </div>
            <div class="stat-card">
                <h3>$<?php echo number_format($stats['ingresos_totales'], 0, ',', '.'); ?></h3>
                <p>Ingresos Totales</p>
            </div>
            <div class="stat-card">
                <h3>$<?php echo number_format($stats['ticket_promedio'], 0, ',', '.'); ?></h3>
                <p>Ticket Promedio</p>
            </div>
        </div>

        <?php if ($resultado->num_rows > 0): ?>
            <div class="alert alert-success">
                <strong>✅ Se encontraron <?php echo $resultado->num_rows; ?> clientes frecuentes</strong>
            </div>

            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID Cliente</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Total Compras</th>
                        <th>Productos Comprados</th>
                        <th>Monto Total</th>
                        <th>Promedio/Compra</th>
                        <th>Primera Compra</th>
                        <th>Última Compra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    while ($fila = $resultado->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><?php echo $fila['id_cliente']; ?></td>
                            <td>
                                <strong>#<?php echo $rank; ?> - <?php echo htmlspecialchars($fila['nombre_cliente']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($fila['email']); ?></td>
                            <td>
                                <span class="badge badge-success">
                                    🛒 <?php echo $fila['total_compras']; ?> compras
                                </span>
                            </td>
                            <td><?php echo $fila['total_productos']; ?> productos</td>
                            <td>
                                <strong>$<?php echo number_format($fila['monto_total_gastado'], 0, ',', '.'); ?></strong>
                            </td>
                            <td>$<?php echo number_format($fila['promedio_por_compra'], 0, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($fila['primera_compra'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($fila['ultima_compra'])); ?></td>
                        </tr>
                    <?php 
                        $rank++;
                    endwhile; 
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">
                <p>⚠️ No hay clientes con más de 2 compras registradas</p>
                <p>Registre más compras para ver resultados en esta consulta avanzada.</p>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <a href="registrar_compra.php" class="btn btn-primary">➕ Registrar Compra</a>
            <a href="listar_compras.php" class="btn btn-secondary">📋 Ver Todas las Compras</a>
            <a href="../index.php" class="btn btn-info">🏠 Volver al Inicio</a>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

<?php $db->close(); ?>