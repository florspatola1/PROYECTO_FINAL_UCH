<?php
session_start();
// Verificar sesión y permisos (administrador o master)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['administrador', 'master'])) {
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

// Obtener ID del donante y turno desde GET
$donante_id = $_GET['donante_id'] ?? null;
$turno_id = $_GET['turno_id'] ?? null;
if (!$donante_id) {
    header('Location: dashboard_admin.php');
    exit;
}

// Obtener información del donante
$consulta_donante = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND rol = 'donante'");
$consulta_donante->execute([$donante_id]);
$donante = $consulta_donante->fetch(PDO::FETCH_ASSOC);

// Verificar si el donante existe, si no existe, 
// redirigir a la dashboard del administrador
if (!$donante) {
    header('Location: dashboard_admin.php');
    exit;
}

// Si hay turno_id, verificar que el turno existe y pertenece al donante
if ($turno_id) {
    $consulta_turno = $pdo->prepare("SELECT * FROM turnos WHERE id = ? AND donante_id = ?");
    $consulta_turno->execute([$turno_id, $donante_id]);
    $turno = $consulta_turno->fetch(PDO::FETCH_ASSOC);
    if (!$turno) {
        $turno_id = null; // Si el turno no existe o no pertenece al donante, ignorar
    }
}

// Variables para mensajes
$mensaje = '';
$tipoMensaje = '';

// Procesar formulario de consulta médica y entrevista medica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'realizar_consulta') {
    $peso = $_POST['peso'] ?? '';
    $altura = $_POST['altura'] ?? '';
    $presion_arterial = $_POST['presion_arterial'] ?? '';
    $frecuencia_cardiaca = $_POST['frecuencia_cardiaca'] ?? '';
    $apto_para_donar = $_POST['apto_para_donar'] ?? '0';
    $observaciones = $_POST['observaciones'] ?? '';

    if (empty($peso) || empty($altura) || empty($presion_arterial) || empty($frecuencia_cardiaca)) {
        $mensaje = 'Por favor, complete todos los campos requeridos.';
        $tipoMensaje = 'danger';
    } else {
        // Insertar evaluación médica
        $consulta_evaluacion_medica = $pdo->prepare("
            INSERT INTO evaluaciones_medicas 
            (donante_id, turno_id, fecha_evaluacion, peso, altura, presion_arterial, frecuencia_cardiaca, apto_para_donar, observaciones, evaluado_por) 
            VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($consulta_evaluacion_medica->execute([$donante_id, $turno_id, $peso, $altura, $presion_arterial, $frecuencia_cardiaca, $apto_para_donar, $observaciones, $_SESSION['user_id']])) {
            $nuevo_estado = 'realizado';

                if($apto_para_donar == '1'){
                    $_SESSION['mensaje_exito'] = 'Consulta médica guardada exitosamente. El turno ha sido marcado como realizado.';
                }else{
                    $nuevo_estado = 'rechazado';
                    $_SESSION['mensaje_exito'] = 'Consulta médica guardada exitosamente. El turno ha sido rechazado debido a la evaluación negativa.';
                }
                $consulta_turno_actualizar_estado = $pdo->prepare("UPDATE turnos SET estado = ? WHERE id = ?");
                $consulta_turno_actualizar_estado->execute([$nuevo_estado, $turno_id]);
            

            // Redirigir al dashboard del administrador
            header('Location: dashboard_admin.php');
            exit;
        } else {
            $mensaje = 'Error al guardar la consulta médica.';
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
    <title>Consulta Médica - Sistema de Donación de Sangre</title>
    <!-- libreria para los estilos de bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- libreria para los iconos de fontawesome -->
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
        <?php if ($mensaje){ ?>
            <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                <?php echo ($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php }?>

      
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-user me-2"></i>Información del Donante</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> <?php echo ($donante['nombre'] . ' ' . $donante['apellido']); ?></p>
                        <p><strong>DNI:</strong> <?php echo ($donante['dni']); ?></p>
                        <p><strong>Email:</strong> <?php echo ($donante['email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Teléfono:</strong> <?php echo ($donante['telefono'] ?? '-'); ?></p>
                        <p><strong>Edad:</strong> <?php echo $donante['edad'] ?? '-'; ?></p>
                        <p><strong>Fecha de registro:</strong> <?php echo date('d/m/Y', strtotime($donante['fecha_registro'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

       
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Consulta Médica</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="realizar_consulta">
                    <?php if ($turno_id) { ?>
                        <input type="hidden" name="turno_id" value="<?php echo ($turno_id); ?>">
                    <?php } ?>
              
                    <div class="info-box">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Complete todos los campos para registrar la evaluación médica del donante:</strong>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="donacion" class="form-label">¿Ud. ha donado sangre en las ultimas 8 semanas?</label>
                            <input type="radio" name="donacion" id="donacion" value="1"> Sí
                            <input type="radio" name="donacion" id="donacion" value="0"> No
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="donacion" class="form-label">¿Padece enfermedades del corazón o pulmones?</label>
                            <input type="radio" name="enfermedades_corazon_pulmones" id="enfermedades_corazon_pulmones" value="1"> Sí
                            <input type="radio" name="enfermedades_corazon_pulmones" id="enfermedades_corazon_pulmones" value="0"> No
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="donacion" class="form-label">¿Consume algún tipo de droga o alcohol con frecuencia?</label>
                            <input type="radio" name="consumo_droga" id="consumo_droga" value="1"> Sí
                            <input type="radio" name="consumo_droga" id="consumo_droga" value="0"> No
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="donacion" class="form-label">¿Se ha realizado cirugías, endoscopia u otro procedimiento quirúrgico en los últimos 6 meses?</label>
                            <input type="radio" name="procedimiento_medico" id="procedimiento_medico" value="1"> Sí
                            <input type="radio" name="procedimiento_medico" id="procedimiento_medico" value="0"> No
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="donacion" class="form-label">¿Se ha realizado tatuajes, piercing o acupuntura en los últimos 6 meses?</label>
                            <input type="radio" name="tatuajes_perforaciones_acupuntura" id="tatuajes_perforaciones_acupuntura" value="1"> Sí
                            <input type="radio" name="tatuajes_perforaciones_acupuntura" id="tatuajes_perforaciones_acupuntura" value="0"> No
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="peso" class="form-label">Peso (kg) *</label>
                            <input type="number" class="form-control" id="peso" name="peso" step="0.1" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="altura" class="form-label">Altura (cm) *</label>
                            <input type="number" class="form-control" id="altura" name="altura" required min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="presion_arterial" class="form-label">Presión Arterial *</label>
                            <input type="text" class="form-control" id="presion_arterial" name="presion_arterial" placeholder="Ej: 120/80" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="frecuencia_cardiaca" class="form-label">Frecuencia Cardíaca (lpm) *</label>
                            <input type="number" class="form-control" id="frecuencia_cardiaca" name="frecuencia_cardiaca" required min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="apto_para_donar" class="form-label">¿Apto para donar? *</label>
                        <select class="form-select" id="apto_para_donar" name="apto_para_donar" required>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Notas adicionales de la consulta..."></textarea>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="dashboard_admin.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Consulta Médica
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