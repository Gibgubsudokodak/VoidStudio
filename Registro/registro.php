<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Parámetros de conexión - REEMPLAZAR con tus datos reales
$host = "localhost";
$usuario = "root";
$contrasena = ""; // contraseña de MySQL
$base_datos = "nombre_base_datos"; // ⚠️ cámbialo por el nombre real de tu base

// Conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Recibir datos del formulario
$nombre = $_POST['nombre'];
$cedula = $_POST['cedula'];
$direccion = $_POST['direccion'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$rol = $_POST['rol'];

// Validación simple de contraseña
if ($password !== $confirm_password) {
    die("Las contraseñas no coinciden.");
}

// Encriptar la contraseña
$password_segura = password_hash($password, PASSWORD_DEFAULT);

// Insertar en la base de datos
$sql = "INSERT INTO usuarios (nombre, cedula, direccion, email, telefono, password, rol) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sssssss", $nombre, $cedula, $direccion, $email, $telefono, $password_segura, $rol);
    if ($stmt->execute()) {
        echo "Usuario registrado correctamente.";
    } else {
        echo "Error al registrar usuario: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error en la preparación de la consulta: " . $conexion->error;
}

$conexion->close();
?>
