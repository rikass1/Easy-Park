<?php
session_start();

// Mostrar erros (podes remover depois)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuração da base de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "easypark";

// Conexão
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Receber dados do formulário
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Verifica se o email é institucional, mas permite admin
if (!preg_match("/^[0-9]+@estudantes\.ips\.pt$/", $email) && $email !== "admin@estudantes.ips.pt") {
    echo "<script>alert('Use apenas o email institucional (ex: numero@estudantes.ips.pt)');window.location.href='../paginas/login.html';</script>";
    exit();
}

// Procura o utilizador pelo email
$sql = "SELECT id_utilizador, nome, tipo, email, password, ativo FROM utilizadores WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// ===============================
// CASO UTILIZADOR EXISTA
// ===============================
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verifica se a conta está ativa
    if (!$user['ativo']) {
        echo "<script>alert('A sua conta está desativada. Contacte a administração.');window.location.href='../paginas/login.html';</script>";
        exit();
    }

    // Login normal (password já definida)
    if ($password === $user['password']) {
        $_SESSION['id_utilizador'] = $user['id_utilizador'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['tipo'] = $user['tipo'];
        $_SESSION['email'] = $user['email'];

        // Redireciona conforme o tipo de utilizador
        if ($user['tipo'] === 'Administrador') {
            header("Location: ../paginas/dashboard.html");
        } else {
            header("Location: ../index.html");
        }
        exit();
    } else {
        echo "<script>alert('Palavra-passe incorreta!');window.location.href='../paginas/login.html';</script>";
        exit();
    }
}

// ===============================
// CASO UTILIZADOR NÃO EXISTA
// ===============================
echo "<script>alert('O seu email não está registado. Contacte a administração.');window.location.href='../paginas/login.html';</script>";
exit();

$stmt->close();
$conn->close();
?>
