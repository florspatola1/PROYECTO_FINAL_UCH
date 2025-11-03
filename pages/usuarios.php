<?php
session_start();
// Verificar sesión y permisos
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'master') {
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

// Variables para mensajes
$mensaje = '';
$tipoMensaje = '';

// Procesar formulario de crear usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_usuario') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $edad = (int)($_POST['edad'] ?? 0);
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'donante';

    if (empty($nombre) || empty($apellido) || empty($dni) || empty($edad) || empty($telefono) || empty($email) || empty($password)) {
        $mensaje = 'Por favor, complete todos los campos.';
        $tipoMensaje = 'danger';
    } else {
        // Verificar si el email o DNI ya existen
        $consulta_usuario_verificar = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR dni = ?");
        $consulta_usuario_verificar->execute([$email, $dni]);
        $existe = $consulta_usuario_verificar->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            $mensaje = 'El email o DNI ya están en uso.';
            $tipoMensaje = 'danger';
        } else {
            // encriptar la contraseña
            $contrasenaEncriptada = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario
            $consulta_usuario_insertar = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, dni, edad, telefono, email, password, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($consulta_usuario_insertar->execute([$nombre, $apellido, $dni, $edad, $telefono, $email, $contrasenaEncriptada, $rol])) {
                $mensaje = 'Usuario creado exitosamente.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al crear el usuario.';
                $tipoMensaje = 'danger';
            }
        }
    }
}


// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $usuario_id = $_POST['usuario_id'] ?? '';
    if ($usuario_id != $_SESSION['user_id']) {
        $consulta_usuario_eliminar = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $consulta_usuario_eliminar->execute([$usuario_id]);
        $mensaje = 'Usuario eliminado exitosamente.';
        $tipoMensaje = 'success';
    } else {
        $mensaje = 'No puedes eliminar tu propio usuario.';
        $tipoMensaje = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Blood Bank System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <link href="../css/usuarios.css" rel="stylesheet">
</head>

<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-heartbeat me-2"></i>
            <span class="text-white">Blood Bank System</span>
        </div>
        <div class="nav-links">
            <span><i class="fas fa-user me-2"></i><?php echo ($_SESSION['user_name'] ?? 'Usuario') ?></span>
            <a href="dashboard_master.php">
                <i class="fas fa-arrow-left me-1"></i>Volver al Dashboard
            </a>
        </div>
    </div>

    <div class="container mt-4">
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                <?php echo ($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
         <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Crear Nuevo Usuario</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="crear_usuario">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" class="form-control" name="apellido" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">DNI</label>
                            <input type="text" class="form-control" name="dni" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Edad</label>
                            <input type="number" class="form-control" name="edad" min="18" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" name="rol" required>
                                <option value="donante">Donante</option>
                                <option value="administrador">Administrador</option>
                              
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-plus me-1"></i>Crear Usuario
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Usuarios</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <?php
                    // Obtener todos los usuarios
                    $consulta_usuarios = $pdo->prepare("SELECT * FROM usuarios ORDER BY rol, nombre, apellido");
                    $consulta_usuarios->execute();
                    $usuarios = $consulta_usuarios->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>DNI</th>
                                <th>Teléfono</th>
                                <th>Edad</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No hay usuarios registrados</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td><?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></td>
                                        <td><?php echo $usuario['email']; ?></td>
                                        <td><?php echo $usuario['dni']; ?></td>
                                        <td><?php echo $usuario['telefono'] ?: 'No disponible'; ?></td>
                                        <td><?php echo $usuario['edad'] ?: 'No especificada'; ?></td>
                                        <td>
                                            <?php
                                            $rolColors = [
                                                'donante' => 'badge bg-info',
                                                'administrador' => 'badge bg-warning',
                                                'master' => 'badge bg-danger'
                                            ];
                                            $badgeClass = $rolColors[$usuario['rol']] ?? 'badge bg-secondary';
                                            ?>
                                            <span class="<?php echo $badgeClass ?>"><?php echo ucfirst($usuario['rol']) ?></span>
                                        </td>

                                        <td>
                                            <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                    <input type="hidden" name="action" value="eliminar">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>