<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Mostrar erros (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$utilizador = "root";
$senha = "";
$dbname = "easypark";
$id_parque = 1;  

try {
    // Conectar usando PDO (não mysqli!)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $utilizador, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query para buscar os dados
    $stmt = $pdo->prepare("
        SELECT 
            lotacao_maxima, 
            lotacao_atual,
            ultima_atualizacao
        FROM parque 
        WHERE id_parque = ?
        LIMIT 1
    ");
    
    $stmt->execute([$id_parque]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        // Sucesso - retornar os dados
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'lotacao_maxima' => (int)$resultado['lotacao_maxima'],
            'lotacao_atual' => (int)$resultado['lotacao_atual'],
            'ultima_atualizacao' => $resultado['ultima_atualizacao'],
            'disponivel' => (int)$resultado['lotacao_maxima'] - (int)$resultado['lotacao_atual']
        ]);
    } else {
        // Nenhum registro encontrado
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Nenhum parque encontrado com ID ' . $id_parque,
            'lotacao_maxima' => 0,
            'lotacao_atual' => 0
        ]);
    }
    
} catch (PDOException $e) {
    // Erro na conexão ou query
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro de base de dados',
        'error_details' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    // Outros erros
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro geral',
        'error_details' => $e->getMessage()
    ]);
}
?>