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

try {
    $conn = new mysqli($host, $utilizador, $senha, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão: " . $conn->connect_error);
    }
    
    // Query para buscar dados de todos os parques
    $sql = "
        SELECT 
            id_parque,
            lotacao_maxima, 
            lotacao_atual,
            ultima_atualizacao
        FROM parque 
        WHERE id_parque IN (1, 2, 3)
        ORDER BY id_parque
    ";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $parques = [];
        $total_max = 0;
        $total_atual = 0;
        
        // Processar cada parque
        while ($row = $result->fetch_assoc()) {
            $id = (int)$row['id_parque'];
            $max = (int)$row['lotacao_maxima'];
            $atual = (int)$row['lotacao_atual'];
            
            $parques[$id] = [
                'id_parque' => $id,
                'lotacao_maxima' => $max,
                'lotacao_atual' => $atual,
                'disponivel' => $max - $atual,
                'ultima_atualizacao' => $row['ultima_atualizacao']
            ];
            
            $total_max += $max;
            $total_atual += $atual;
        }
        
        // Sucesso - retornar os dados de todos os parques
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'parques' => $parques,
            'total' => [
                'lotacao_maxima' => $total_max,
                'lotacao_atual' => $total_atual,
                'disponivel' => $total_max - $total_atual
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Nenhum registro encontrado
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Nenhum parque encontrado na base de dados',
            'parques' => [],
            'total' => [
                'lotacao_maxima' => 0,
                'lotacao_atual' => 0,
                'disponivel' => 0
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
    
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