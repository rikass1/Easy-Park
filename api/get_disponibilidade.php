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
    $conn = new mysqli($host, $utilizador, $senha, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão: " . $conn->connect_error);
    }
    
    // Query para buscar os dados
    $stmt = $conn->prepare("
        SELECT 
            lotacao_maxima, 
            lotacao_atual,
            ultima_atualizacao
        FROM parque 
        WHERE id_parque = ?
        LIMIT 1
    ");
    
    $stmt->bind_param("i", $id_parque);
    $stmt->execute();
    $result = $stmt->get_result();
    $resultado = $result->fetch_assoc();
    
    if ($resultado) {
        // Sucesso - retornar os dados
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'lotacao_maxima' => (int)$resultado['lotacao_maxima'],
            'lotacao_atual' => (int)$resultado['lotacao_atual'],
            'ultima_atualizacao' => $resultado['ultima_atualizacao'],
            'disponivel' => (int)$resultado['lotacao_maxima'] - (int)$resultado['lotacao_atual']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Nenhum registro encontrado
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Nenhum parque encontrado com ID ' . $id_parque,
            'lotacao_maxima' => 0,
            'lotacao_atual' => 0
        ], JSON_UNESCAPED_UNICODE);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Erro na conexão ou query
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro de base de dados',
        'error_details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>