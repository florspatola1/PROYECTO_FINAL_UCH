<?php


function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}


function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

function getNombreCompleto($usuario)
{
    return $usuario['nombre'] . ' ' . $usuario['apellido'];
}


function getRolBadgeClass($rol)
{
    $badges = [
        'master' => 'danger',
        'administrador' => 'warning',
        'donante' => 'info'
    ];
    return $badges[$rol] ?? 'secondary';
}

function validarEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


function sanitizar($texto)
{
    return (strip_tags(trim($texto)));
}

function mostrarError($mensaje)
{
    echo "<div class='alert alert-danger'>$mensaje</div>";
}


function mostrarExito($mensaje)
{
    echo "<div class='alert alert-success'>$mensaje</div>";
}
