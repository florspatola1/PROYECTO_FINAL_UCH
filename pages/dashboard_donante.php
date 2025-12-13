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
// Obtener turnos del donante
$consulta_turnos = $pdo->prepare("SELECT * FROM turnos WHERE donante_id = ? ORDER BY fecha DESC, hora DESC");
$consulta_turnos->execute([$_SESSION['user_id']]);
$turnos = $consulta_turnos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Donante - Sistema de Donación de Sangre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard_donante.css">
</head>

<body>    
    <div class="header">
        <div class="logo">
            <i class="fas fa-heartbeat me-2"></i>
            <span class="text-white">Blood Bank System</span>
        </div>
        <div class="nav-links">
            <span><i class="fas fa-user me-2"></i><?php echo ($_SESSION['user_name'] ?? 'Usuario') ?></span>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
            </a>
        </div>
    </div>

    <div class="container">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Solicitar Nuevo Turno</h4>
                        <p class="text-muted mb-0">Reserva tu turno para donar sangre</p>
                    </div>
                    <a href="solicitar_turno.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Solicitar Turno
                    </a>
                </div>
            </div>
        </div>
     
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Mis Turnos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($turnos)){ ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-info-circle me-2"></i>No tienes turnos programados. ¡Solicita uno ahora!
                    </div>
                <?php }else{ ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Motivo</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($turnos as $turno){ ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($turno['fecha'])); ?></td>
                                        <td><?php echo ($turno['hora']); ?></td>
                                        <td><?php echo ($turno['motivo'] ?? '-'); ?></td>
                                        <td><?php echo ($turno['estado'] ?? '-') ?> </td>
                                        <td><?php echo ($turno['observaciones'] ?? '-'); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>