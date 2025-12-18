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
    //PDO libreria de php para conexion a base de datos
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
    <title>Iniciar Sesión - Blood Bank System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/login.css">
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
        <div class="login-card ">
          
            <div class="card-header">
                <span>Iniciar Sesión</span>
            </div>

            <!-- Contenido del formulario -->
            <div class="form-content">
                <?php
                // Procesar login
                if ($_POST) {
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? '';

                    if ($email && $password) {
                        // Buscar usuario en la base de datos
                        $consulta_usuario = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
                        $consulta_usuario->execute([$email]);
                        $usuario = $consulta_usuario->fetch(PDO::FETCH_ASSOC);

                        if ($usuario) {
                            // Login exitoso
                            $_SESSION['user_id'] = $usuario['id'];
                            $_SESSION['user_role'] = $usuario['rol'];
                            $_SESSION['user_email'] = $usuario['email'];
                            $_SESSION['user_name'] = $usuario['nombre'] . ' ' . $usuario['apellido'];

                            echo "<div class='alert alert-success'>¡Bienvenido " . $usuario['nombre'] . "!</div>";
                            // Redirigir según el rol
                            $rol = $usuario['rol'];
                            $dashboard = '';
                            if ($rol === 'master') {
                                $dashboard = 'dashboard_master.php';
                            } elseif ($rol === 'administrador') {
                                $dashboard = 'dashboard_admin.php';
                            } elseif ($rol === 'donante') {
                                $dashboard = 'dashboard_donante.php';
                            }
                            echo "<script>setTimeout(() => window.location.href = '$dashboard', 1500);</script>";
                        } else {
                            echo "<script>alert('Email incorrecto');</script>";
                        }
                    }
                }
                ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="fas fa-envelope me-2"></i>
                            </div>
                            <input type="email" id="email" name="email" class="input-field" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" id="password" name="password" class="input-field" required>
                        </div>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-arrow-right me-2"></i>
                        <span>Iniciar Sesión</span>
                    </button>
                </form>

                <div class="register-link">
                    ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>