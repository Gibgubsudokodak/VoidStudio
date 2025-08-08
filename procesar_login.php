<?php
session_start();

// Conexión a la base de datos
$host = 'localhost';
$db = 'taller';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos del formulario
$cedula = $_POST['ci'] ?? '';
$password = $_POST['password'] ?? '';
$rol_form = $_POST['rol'] ?? '';

// Validar campos
if (empty($cedula) || empty($password) || empty($rol_form)) {
    die("Todos los campos son obligatorios.");
}

// Buscar usuario por cédula
$stmt = $conn->prepare("SELECT id_usuario, nombre, contraseña_hash FROM usuarios WHERE cedula = ?");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id_usuario, $nombre, $hash);
    $stmt->fetch();

    // Verificar contraseña
    if (password_verify($password, $hash)) {
        $_SESSION['id_usuario'] = $id_usuario;
        $_SESSION['nombre'] = $nombre;

        $rol_detectado = null;

        if ($rol_form === 'cliente') {
            $check_cliente = $conn->prepare("SELECT id_cliente FROM clientes WHERE id_usuario = ?");
            $check_cliente->bind_param("i", $id_usuario);
            $check_cliente->execute();
            $check_cliente->store_result();
            if ($check_cliente->num_rows > 0) {
                $rol_detectado = 'cliente';
            }
            $check_cliente->close();
        }

        if ($rol_form === 'admin' || $rol_form === 'taller') {
            $check_admin = $conn->prepare("SELECT tipo FROM administradores WHERE id_usuario = ?");
            $check_admin->bind_param("i", $id_usuario);
            $check_admin->execute();
            $check_admin->bind_result($tipo);
            if ($check_admin->fetch()) {
                if (($rol_form === 'admin' && $tipo === 'administrador') ||
                    ($rol_form === 'taller' && $tipo === 'tecnico')) {
                    $rol_detectado = $rol_form;
                }
            }
            $check_admin->close();
        }

        if ($rol_detectado === 'cliente') {
            header("Location: /Gestion_Proyecto/src/cliente/index.php");
        } elseif ($rol_detectado === 'taller') {
            header("Location: /Gestion_Proyecto/src/taller/index.php");
        } elseif ($rol_detectado === 'admin') {
            header("Location: /Gestion_Proyecto/src/admin/index.php");
        } else {
            echo "⚠️ El rol seleccionado no coincide con el usuario.";
        }
        exit();
    } else {
        echo "⚠️ Contraseña incorrecta.";
    }
} else {
    echo "⚠️ No se encontró un usuario con esa cédula.";
}

$stmt->close();
$conn->close();
?>
