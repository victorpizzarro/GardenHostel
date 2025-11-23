<?php
/*
    Endpoint pra cadastrar um novo usuário (CLIENTE).
    Método: POST
    Recebe: JSON { "nome_completo": "...", "email": "...", "senha": "...", ... }
    Retorna: JSON { "status": "sucesso", "id_usuario": "..." } ou { "status": "erro", ... }
*/


require_once '../config.php';


$nome = $dadosRecebidos['nome_completo'];
$email = $dadosRecebidos['email'];
$senha = $dadosRecebidos['senha'];
$doc_tipo = $dadosRecebidos['documento_tipo'];
$doc_num = $dadosRecebidos['documento_numero'];
$data_nasc = $dadosRecebidos['data_nascimento'];
$celular = $dadosRecebidos['telefone_celular'];


$tipo_usuario = 'CLIENTE';


$cep = isset($dadosRecebidos['cep']) ? $dadosRecebidos['cep'] : null;
$logradouro = isset($dadosRecebidos['logradouro']) ? $dadosRecebidos['logradouro'] : null;
$numero = isset($dadosRecebidos['numero']) ? $dadosRecebidos['numero'] : null;
$complemento = isset($dadosRecebidos['complemento']) ? $dadosRecebidos['complemento'] : null;
$bairro = isset($dadosRecebidos['bairro']) ? $dadosRecebidos['bairro'] : null;
$cidade = isset($dadosRecebidos['cidade']) ? $dadosRecebidos['cidade'] : null;
$estado = isset($dadosRecebidos['estado']) ? $dadosRecebidos['estado'] : null;


if (empty($nome) || empty($email) || empty($senha)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Nome, email e senha são obrigatórios.'
    ]);
    exit();
}


$conexao->begin_transaction();

try {
    
    $sql_usuario = "INSERT INTO Usuarios 
                        (nome_completo, email, senha, documento_tipo, documento_numero, data_nascimento, telefone_celular, tipo_usuario)
                    VALUES 
                        ('$nome', '$email', '$senha', '$doc_tipo', '$doc_num', '$data_nasc', '$celular', '$tipo_usuario')";

    if (!$conexao->query($sql_usuario)) {
       
        throw new Exception('Erro ao cadastrar usuário: ' . $conexao->error);
    }

    
    $novo_usuario_id = $conexao->insert_id;

    
    $sql_endereco = "INSERT INTO Enderecos 
                        (fk_usuario_id, cep, logradouro, numero, complemento, bairro, cidade, estado)
                     VALUES 
                        ($novo_usuario_id, '$cep', '$logradouro', '$numero', '$complemento', '$bairro', '$cidade', '$estado')";
    
    if (!$conexao->query($sql_endereco)) {
        // Se a query falhar, lança uma exceção
        throw new Exception('Erro ao cadastrar endereço: ' . $conexao->error);
    }

    
    $conexao->commit();
    
    http_response_code(201); // 201 Created
    echo json_encode([
        'status' => 'sucesso',
        'mensagem' => 'Usuário cadastrado com sucesso.',
        'id_usuario' => $novo_usuario_id
    ]);

} catch (Exception $e) {
    
    $conexao->rollback();
    
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage()
    ]);
}
?>