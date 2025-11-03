<?php
// Iniciar sesión
session_start();

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
    <title>Registro - Blood Bank System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/register.css">
</head>

<body>
  
    <div class="header">
        <div class="logo">
            <i class="fas fa-heartbeat me-2"></i>
            <span class="text-white">Blood Bank System</span>
        </div>
        <div class="nav-links">
            <a href="login.php">Iniciar Sesión</a>
            <a href="register.php">Registrarse</a>
        </div>
    </div>

    <div class="container">
        <div class="register-card">
            
            <div class="card-header">
                <span class="card-header-icon"></span>
                <h2>Registro de Usuario</h2>
            </div>

            <div class="form-content">
                <?php
                // Procesar registro
                if ($_POST) {
                    $nombre = $_POST['nombre'] ?? '';
                    $apellido = $_POST['apellido'] ?? '';
                    $dni = $_POST['dni'] ?? '';
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? '';
                    $telefono = $_POST['telefono'] ?? '';
                    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

                    // Calcular edad
                    $edad = 0;
                    if ($fecha_nacimiento) {
                        $fecha_nac = new DateTime($fecha_nacimiento);
                        $hoy = new DateTime();
                        $edad = $hoy->diff($fecha_nac)->y;
                    }

                    if ($nombre && $apellido && $dni && $email && $password) {
                        // Verificar si el email o DNI ya existen
                        $consulta_usuario = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR dni = ?");
                        $consulta_usuario->execute([$email, $dni]);

                        if ($consulta_usuario->fetch(PDO::FETCH_ASSOC)) {
                            echo "<script>alert('El email o DNI ya están registrados');</script>";
                        } else {
                            // Insertar nuevo usuario (contraseña en texto plano)
                            $consulta_usuario_insertar = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, dni, email, password, telefono, edad, rol) VALUES (?, ?, ?, ?, ?, ?, ?, 'donante')");

                            if ($consulta_usuario_insertar->execute([$nombre, $apellido, $dni, $email, $password, $telefono, $edad])) {
                                echo "<script>alert('Usuario registrado exitosamente. Ya puedes iniciar sesión.');</script>";
                                echo "<script>setTimeout(() => window.location.href = 'login.php', 2000);</script>";
                            } else {
                                echo "<script>alert('Error al registrar usuario. Intente nuevamente.');</script>";
                            }
                        }
                    } else {
                        echo "<script>alert('Por favor complete todos los campos obligatorios');</script>";
                    }
                }
                ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre *</label>
                            <input type="text" id="nombre" name="nombre" class="input-field" required>
                        </div>

                        <div class="form-group">
                            <label for="apellido">Apellido *</label>
                            <input type="text" id="apellido" name="apellido" class="input-field" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dni">DNI *</label>
                            <input type="text" id="dni" name="dni" class="input-field" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="input-field" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Contraseña *</label>
                            <input type="password" id="password" name="password" class="input-field" required>
                        </div>

                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono" class="input-field">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="input-field">
                    </div>

                    <button type="submit" class="register-btn">
                        <span>→</span>
                        <span>Registrarse</span>
                    </button>
                </form>

                <div class="login-link">
                    ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>