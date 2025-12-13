<?php
session_start();
// Verificar sesión y permisos
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'donante') {
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

// Variables para mensajes de alertas
$mensaje = '';
$tipoMensaje = '';

// Procesar formulario de solicitud de turno
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $motivo = trim($_POST['motivo'] ?? '');

    if (empty($fecha) || empty($hora) || empty($motivo)) {
        $mensaje = 'Por favor, complete todos los campos.';
        $tipoMensaje = 'danger';
    } else {
        // Insertar turno
        $consulta_turno_solicitar = $pdo->prepare("INSERT INTO turnos (donante_id, fecha, hora, motivo, estado) VALUES (?, ?, ?, ?, 'pendiente')");
        if ($consulta_turno_solicitar->execute([$_SESSION['user_id'], $fecha, $hora, $motivo])) {
            $mensaje = 'Turno solicitado exitosamente.';
            $tipoMensaje = 'success';
            // Limpiar campos después de 2 segundos y redirigir a la dashboard del donante
            echo "<script>setTimeout(() => window.location.href = 'dashboard_donante.php', 2000);</script>";
        } else {
            $mensaje = 'Error al solicitar el turno.';
            $tipoMensaje = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Turno - Sistema de Donación de Sangre</title>
    <!-- libreria para los estilos de bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- libreria para los iconos de fontawesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/solicitar_turno.css">
</head>

<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-heartbeat me-2"></i>
            <span class="text-white">Blood Bank System</span>
        </div>
        <div class="nav-links">
            <span><i class="fas fa-user me-2"></i><?php echo ($_SESSION['user_name'] ?? 'Usuario') ?></span>
            <a href="dashboard_donante.php">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <div class="container">
        <?php if ($mensaje){ ?>
            <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                <?php echo ($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Solicitar Nuevo Turno</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label">Fecha del Turno</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="hora" class="form-label">Hora del Turno</label>
                            <input type="time" class="form-control" id="hora" name="hora" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="4" placeholder="Describe el motivo de tu turno..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="dashboard_donante.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i>Solicitar Turno
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- libreria para los scripts de bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>