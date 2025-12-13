<?php
// Iniciar sesión
session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
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
    <title>Dashboard Master - Blood Bank System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard_master.css">
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

    <div class="container mt-4">
        <?php
     
        // Obtener estadísticas generales
        $consulta_total_usuarios = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios");
        $consulta_total_usuarios->execute();
        $total_usuarios = $consulta_total_usuarios->fetch()['total'];

        $consulta_total_donantes = $pdo->prepare("SELECT COUNT(*) as total_donantes FROM usuarios WHERE rol = 'donante'");
        $consulta_total_donantes->execute();
        $total_donantes = $consulta_total_donantes->fetch()['total_donantes'];

        $consulta_total_admins = $pdo->prepare("SELECT COUNT(*) as total_admins FROM usuarios WHERE rol = 'administrador'");
        $consulta_total_admins->execute();
        $total_admins = $consulta_total_admins->fetch()['total_admins'];

        $consulta_total_turnos = $pdo->prepare("SELECT COUNT(*) as total_turnos FROM turnos");
        $consulta_total_turnos->execute();
        $total_turnos = $consulta_total_turnos->fetch()['total_turnos'];

        $consulta_total_evaluaciones = $pdo->prepare("SELECT COUNT(*) as total_evaluaciones FROM evaluaciones_medicas");
        $consulta_total_evaluaciones->execute();
        $totalEvaluaciones = $consulta_total_evaluaciones->fetch()['total_evaluaciones'];

        ?>

        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card stat-card bg-primary text-white">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?php echo $total_usuarios ?></div>
                    <div class="stat-label">Total Usuarios</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card stat-card bg-success text-white">
                    <div class="stat-icon"><i class="fas fa-heart"></i></div>
                    <div class="stat-number"><?php echo $total_donantes ?></div>
                    <div class="stat-label">Donantes</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card stat-card bg-info text-white">
                    <div class="stat-icon"><i class="fas fa-user-md"></i></div>
                    <div class="stat-number"><?php echo $total_admins ?></div>
                    <div class="stat-label">Administradores</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="card stat-card bg-warning text-white">
                    <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                    <div class="stat-number"><?php echo$total_turnos?></div>
                    <div class="stat-label">Turnos</div>
                </div>
            </div>
 
            <div class="col-md-2">
                <div class="card stat-card bg-danger text-white">
                    <div class="stat-icon"><i class="fas fa-stethoscope"></i></div>
                    <div class="stat-number"><?php echo $totalEvaluaciones ?></div>
                    <div class="stat-label">Evaluaciones</div>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">

                </div>
            </div>

            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-crown text-danger me-2"></i>Panel Master
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Usuario:</strong> <?php echo ($_SESSION['user_name']) ?></p>
                                <p><strong>Email:</strong> <?php echo ($_SESSION['user_email']) ?></p>
                                <p><strong>Rol:</strong> <?php echo ($_SESSION['user_role']) ?></p>

                            </div>
                            <div class="col-md-4">
                                <h6>Acciones del Sistema</h6>
                                <div class="d-grid gap-2">
                                    <a href="usuarios.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-users me-1"></i>Gestionar Usuarios
                                    </a>
                                    <a href="#" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-calendar me-1"></i>Gestionar Turnos
                                    </a>
                                    <a href="#" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-stethoscope me-1"></i>Ver Evaluaciones
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle text-danger me-2"></i>Información del Sistema
                        </h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Versión:</strong> 1.0.0</p>
                        <p><strong>Última actualización:</strong> <?php echo date('d/m/Y') ?></p>
                        <p><strong>Estado:</strong> <span class="text-success">Activo</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>