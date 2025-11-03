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
    <title>Evaluaciones Médicas - Sistema de Donación de Sangre</title>
    <link rel="stylesheet" href="../css/evaluaciones.css">
</head>

<body>
    <div class="header">
        <h1>Evaluaciones Médicas</h1>
    </div>

    <div class="container">
        <a href="dashboard" class="back-btn">← Volver al Dashboard</a>

        <div class="evaluaciones-table">
            <?php
            // Obtener evaluaciones médicas
            $consulta_evaluaciones = $pdo->query("
                SELECT e.*, u.nombre, u.apellido, u.email 
                FROM evaluaciones_medicas e 
                JOIN usuarios u ON e.donante_id = u.id 
                ORDER BY e.fecha_evaluacion DESC
            ");
            $evaluaciones = $consulta_evaluaciones->fetchAll();
            ?>

            <?php if (empty($evaluaciones)): ?>
                <div class="no-evaluaciones">
                    <h3>No hay evaluaciones médicas registradas</h3>
                    <p>Las evaluaciones aparecerán aquí cuando se realicen</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donante</th>
                            <th>Email</th>
                            <th>Fecha Evaluación</th>
                            <th>Peso (kg)</th>
                            <th>Altura (cm)</th>
                            <th>Presión Arterial</th>
                            <th>Frecuencia Cardíaca</th>
                            <th>Resultado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluaciones as $eval): ?>
                            <tr>
                                <td><?php echo $eval['nombre'] . ' ' . $eval['apellido']; ?></td>
                                <td><?php echo $eval['email']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($eval['fecha_evaluacion'])); ?></td>
                                <td><?php echo $eval['peso']; ?></td>
                                <td><?php echo $eval['altura']; ?></td>
                                <td><?php echo $eval['presion_arterial']; ?></td>
                                <td><?php echo $eval['frecuencia_cardiaca']; ?></td>
                                <td>
                                    <span class="<?php echo $eval['apto_para_donar'] ? 'apto' : 'no-apto'; ?>">
                                        <?php echo $eval['apto_para_donar'] ? 'Apto' : 'No Apto'; ?>
                                    </span>
                                </td>
                                <td><?php echo $eval['observaciones'] ?: 'Sin observaciones'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>