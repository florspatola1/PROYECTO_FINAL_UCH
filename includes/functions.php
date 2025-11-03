<?php

/**
 * Funciones auxiliares para el sistema
 */

/**
 * Hashea una contraseña
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifica una contraseña
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Formatea el nombre completo de un usuario
 */
function getNombreCompleto($usuario)
{
    return $usuario['nombre'] . ' ' . $usuario['apellido'];
}

/**
 * Obtiene el badge CSS para un rol
 */
function getRolBadgeClass($rol)
{
    $badges = [
        'master' => 'danger',
        'administrador' => 'warning',
        'donante' => 'info'
    ];
    return $badges[$rol] ?? 'secondary';
}

/**
 * Valida que un email sea válido
 */
function validarEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitiza una entrada de texto
 */
function sanitizar($texto)
{
    return (strip_tags(trim($texto)));
}

/**
 * Muestra un mensaje de error
 */
function mostrarError($mensaje)
{
    echo "<div class='alert alert-danger'>$mensaje</div>";
}

/**
 * Muestra un mensaje de éxito
 */
function mostrarExito($mensaje)
{
    echo "<div class='alert alert-success'>$mensaje</div>";
}
