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

// Obtener y sanitizar datos del formulario
$cedula = $conn->real_escape_string($_POST['ci'] ?? '');
$password = $_POST['password'] ?? '';
$rol_form = $conn->real_escape_string($_POST['rol'] ?? '');

// Validar campos
if (empty($cedula) || empty($password) || empty($rol_form)) {
    $_SESSION['error'] = "Todos los campos son obligatorios.";
    header("Location: index.php");
    exit();
}

// Buscar usuario por cédula
$stmt = $conn->prepare("SELECT id_usuario, nombre, contraseña_hash FROM usuarios WHERE cedula = ?");
if (!$stmt) {
    $_SESSION['error'] = "Error en la preparación de la consulta: " . $conn->error;
    header("Location: index.php");
    exit();
}

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

        if ($rol_detectado) {
            $_SESSION['rol'] = $rol_detectado;
            switch ($rol_detectado) {
                case 'cliente':
                    header("Location: cliente/index.php");
                    break;
                case 'taller':
                    header("Location: taller/index.php");
                    break;
                case 'admin':
                    header("Location: admin/index.php");
                    break;
            }
            exit();
        } else {
            $_SESSION['error'] = "El rol seleccionado no coincide con el usuario.";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Contraseña incorrecta.";
        header("Location: index.php");
        exit();
    }
} else {
    $_SESSION['error'] = "No se encontró un usuario con esa cédula.";
    header("Location: index.php");
    exit();
}

$stmt->close();
$conn->close();
?>