<?php
session_start();
// Verificar sesión y permisos, 
// si no es administrador, redirigir a la página de login y salir
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

// Procesamos la cancelación del turno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancelar_turno') {
    $turno_id = $_POST['turno_id'] ?? '';
    if ($turno_id) {
        $turno_cancelado = $pdo->prepare("UPDATE turnos SET estado = 'cancelado' WHERE id = ?");
        $turno_cancelado->execute([$turno_id]);
    }
}

// Obtener todos los turnos con información del donante
$consulta_turnos = $pdo->prepare("
    SELECT t.*, u.nombre, u.apellido, u.email, u.telefono 
    FROM turnos t 
    JOIN usuarios u ON t.donante_id = u.id 
    ORDER BY t.fecha DESC, t.hora DESC
");
$consulta_turnos->execute();
$turnos = $consulta_turnos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - Sistema de Donación de Sangre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard_admin.css">
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
        
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-list me-2"></i>Gestión de Turnos</h4>
            </div>
            <div class="card-body">
                <?php if (empty($turnos)){ ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>No hay turnos registrados.
                    </div>
                <?php }else{ ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
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
                                <?php foreach ($turnos as $turno){?>
                                    <tr>
                                        <td><?php echo $turno['id']; ?></td>
                                        <td><?php echo ($turno['nombre'] . ' ' . $turno['apellido']); ?></td>
                                        <td><?php echo ($turno['email']); ?></td>
                                        <td><?php echo ($turno['telefono'] ?? '-'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($turno['fecha'])); ?></td>
                                        <td><?php echo ($turno['hora']); ?></td>
                                        <td><?php echo ($turno['motivo'] ?? '-'); ?></td>
                                        <td><?php echo ($turno['estado'] ?? '-'); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                
                                                <?php if ($turno['estado'] != 'cancelado' && $turno['estado'] != 'realizado' && $turno['estado'] != 'rechazado'){ ?>
                                                <a href="consulta_medica.php?donante_id=<?php echo $turno['donante_id']; ?>&turno_id=<?php echo $turno['id']; ?>" class="btn btn-sm btn-primary me-2" title="Realizar consulta médica">
                                                    <i class="fas fa-stethoscope"></i>
                                                </a>
                                                
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="turno_id" value="<?php echo $turno['id']; ?>">
                                                        <input type="hidden" name="action" value="cancelar_turno">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Cancelar turno" onclick="return confirm('¿Está seguro de cancelar este turno?')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                <?php } ?>
                                            </div>
                                        </td>
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