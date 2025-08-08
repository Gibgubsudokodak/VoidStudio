<?php
// Conexión
$host = 'localhost';
$db = 'taller';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

// Datos del formulario
$nombre = $_POST['nombre'];
$cedula = $_POST['cedula'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$rol = $_POST['rol'];

$direccion = $_POST['direccion'];
$telefono = $_POST['telefono'];

if ($password !== $confirm_password) {
    die("Las contraseñas no coinciden.");
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

// 1. Insertar en usuarios
$stmt1 = $conn->prepare("INSERT INTO usuarios (nombre, cedula, email, contraseña_hash) VALUES (?, ?, ?, ?)");
$stmt1->bind_param("ssss", $nombre, $cedula, $email, $password_hash);

if ($stmt1->execute()) {
    $id_usuario = $stmt1->insert_id;

    // 2. Insertar en tabla correspondiente según rol
    if ($rol === 'cliente') {
        $stmt2 = $conn->prepare("INSERT INTO clientes (id_usuario, direccion, telefono) VALUES (?, ?, ?)");
        $stmt2->bind_param("iss", $id_usuario, $direccion, $telefono);
    } elseif ($rol === 'administrador' || $rol === 'tecnico') {
        $stmt2 = $conn->prepare("INSERT INTO administradores (id_usuario, tipo) VALUES (?, ?)");
        $stmt2->bind_param("is", $id_usuario, $rol);
    } else {
        die("Rol no válido.");
    }

    if ($stmt2->execute()) {
        // Redireccionar a la carpeta principal (por ejemplo, a ../index.php)
        header("Location: ../index.php");
        exit(); // Muy importante para detener el script
    } else {
        echo "Error al registrar el rol: " . $stmt2->error;
    }
    

    $stmt2->close();
} else {
    echo "Error al registrar usuario: " . $stmt1->error;
}

$stmt1->close();
$conn->close();
?>
