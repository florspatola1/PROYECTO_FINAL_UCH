<?php
session_start();
// Verificar sesión y permisos
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header('Location: login.php');
    exit;
}

// Configuración de BD
$host = 'localhost';
$dbname = 'proyecto_final_uch';
$username = 'root';
$password = '';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Turnos - Sistema de Donación de Sangre</title>
    <link rel="stylesheet" href="../css/gestionar_turnos.css">
</head>

<body>
    <div class="header">
        <h1>Gestionar Turnos de Donación</h1>
    </div>

    <div class="container">
        <a href="dashboard" class="back-btn">← Volver al Dashboard</a>

        <div class="turnos-table">
            <?php
            // Procesar acciones
            if ($_POST && isset($_POST['action'])) {
                $turno_id = $_POST['turno_id'] ?? '';
                $action = $_POST['action'] ?? '';

                if ($turno_id && $action) {
                    if ($action === 'confirmar') {
                        $consulta_turno_confirmar = $pdo->prepare("UPDATE turnos SET estado = 'confirmado' WHERE id = ?");
                        $consulta_turno_confirmar->execute([$turno_id]);
                        mostrarExito("Turno confirmado exitosamente");
                    } elseif ($action === 'cancelar') {
                        $consulta_turno_cancelar = $pdo->prepare("UPDATE turnos SET estado = 'cancelado' WHERE id = ?");
                        $consulta_turno_cancelar->execute([$turno_id]);
                        mostrarExito("Turno cancelado exitosamente");
                    }
                }
            }

            // Obtener todos los turnos con información del donante
            $consulta_turnos = $pdo->query("
                SELECT t.*, u.nombre, u.apellido, u.email, u.telefono 
                FROM turnos t 
                JOIN usuarios u ON t.donante_id = u.id 
                ORDER BY t.fecha DESC, t.hora DESC
            ");
            $turnos = $consulta_turnos->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Donante</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($turnos as $turno): ?>
                        <tr>
                            <td><?php echo $turno['nombre'] . ' ' . $turno['apellido']; ?></td>
                            <td><?php echo $turno['email']; ?></td>
                            <td><?php echo $turno['telefono'] ?: 'No disponible'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($turno['fecha'])); ?></td>
                            <td><?php echo date('H:i', strtotime($turno['hora'])); ?></td>
                            <td><?php echo $turno['motivo'] ?: 'Sin motivo'; ?></td>
                            <td>
                                <span class="estado <?php echo $turno['estado']; ?>">
                                    <?php echo ucfirst($turno['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($turno['estado'] === 'pendiente'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="turno_id" value="<?php echo $turno['id']; ?>">
                                        <input type="hidden" name="action" value="confirmar">
                                        <button type="submit" class="btn-small btn-success">Confirmar</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="turno_id" value="<?php echo $turno['id']; ?>">
                                        <input type="hidden" name="action" value="cancelar">
                                        <button type="submit" class="btn-small btn-danger">Cancelar</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #666;">Sin acciones</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>