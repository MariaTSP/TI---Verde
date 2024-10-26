<?php
session_start(); // Inicia a sessão

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sustentabilidade";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Inicializa a pontuação total e a pergunta atual
if (!isset($_SESSION['pontuacaoTotal'])) {
    $_SESSION['pontuacaoTotal'] = 0;
}
if (!isset($_SESSION['perguntaAtual'])) {
    $_SESSION['perguntaAtual'] = 1; // Começa com a primeira pergunta
}

// Gera um token único para o formulário
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32)); // Gera um token aleatório
}

// Perguntas do quiz
$perguntas = [
    1 => [
        "O que é TI Verde?",
        [
            "Uma metodologia de desenvolvimento ágil." => 0,
            "Práticas de tecnologia focadas em reduzir o impacto ambiental." => 10,
            "Um software de gerenciamento de energia." => 0,
            "Um conceito que incentiva a compra de novos dispositivos eletrônicos." => 0,
            "Uma política de segurança da informação." => 0,
        ],
    ],
    2 => [
        "Quais são os 5Rs da sustentabilidade?",
        [
            "Reduzir, Reutilizar, Reciclar, Repensar, Recusar." => 10,
            "Renovar, Reutilizar, Reduzir, Reparar, Reciclar." => 0,
            "Reciclar, Renovar, Remover, Reduzir, Responder." => 0,
            "Reduzir, Reutilizar, Reciclar, Reparar, Reeducar." => 0,
            "Reduzir, Reciclar, Renovar, Reutilizar, Reestruturar." => 0,
        ],
    ],
    3 => [
        "Qual é o principal objetivo da TI Verde?",
        [
            "Aumentar a eficiência dos processos empresariais." => 0,
            "Reduzir o uso de recursos naturais e minimizar o impacto ambiental da tecnologia." => 10,
            "Garantir a segurança da informação." => 0,
            "Melhorar o desempenho de sistemas e aplicativos." => 0,
            "Criar mais empregos na área de TI." => 0,
        ],
    ],
    4 => [
        "O que é um ciclo de vida de um produto?",
        [
            "Tempo de uso do produto." => 0,
            "Processo de fabricação do produto." => 0, 
            "Etapas que o produto passa desde a produção até o descarte." => 10,
            "O tempo que um produto permanece em estoque." => 0,
            "O impacto ambiental do produto." => 0,
        ],
    ],
    5 => [
        "Como podemos aplicar o conceito de 'Reduzir' no uso da tecnologia?",
        [
            "Comprando mais aparelhos eletrônicos modernos." => 0,
            "Utilizando produtos que não podem ser reciclados." => 0,
            "Usando baterias descartáveis para todos os dispositivos." => 0,
            "Substituindo todos os dispositivos antigos por novos." => 0,
            "Evitando o consumo excessivo de eletrônicos e prolongando sua vida útil." => 10,
        ],
    ],
    6 => [
        "Como o uso de energias renováveis se relaciona com a TI Verde?",
        [
            "Aumenta o consumo de energia não renovável." => 0,
            "Aumenta o uso de recursos naturais sem controle." => 0,
            "Impede o desenvolvimento de novas tecnologias." => 0, 
            "Diminui o impacto ambiental das operações tecnológicas." => 10,
            "Não tem impacto significativo no consumo de energia." => 0, 
        ],
    ],
    7 => [
        "Qual é um dos principais benefícios da computação em nuvem para a TI Verde?",
        [
            "Aumento de custos operacionais."  => 0,
            "Maior uso de hardware físico nas empresas." => 0,
            "Dificuldade de acesso remoto aos dados." => 0,
            "Redução do consumo de energia e espaço físico para servidores."  => 10,
            "Aumento da dependência de hardware local." => 0,
        ],
    ],    
    8 => [
            "Qual prática não está alinhada com a sustentabilidade?",
            [
                "Comprar equipamentos eletrônicos constantemente para sempre ter o mais recente." => 10,
                "Prolongar a vida útil de dispositivos eletrônicos." => 0,
                "Utilizar softwares que economizam energia."  => 0,
                "Reciclar corretamente o lixo eletrônico." => 0,
                "Reutilizar dispositivos eletrônicos antigos." => 0, 
            ],
        ],
    9 => [
            "O que significa 'Recusar' no contexto dos 5 Rs?",
            [
                "Evitar o uso de eletricidade." => 0,
                "Recusar-se a comprar qualquer coisa nova." => 0, 
                "Negar produtos que prejudicam o meio ambiente ou possuem ciclo de vida insustentável." => 10,
                "Rejeitar a compra de produtos recicláveis." => 0,
                "Recusar o uso de qualquer tipo de plástico." => 0,
            ],
        ],
    10 => [
            "Qual ação está relacionada ao conceito de 'Reutilizar' nos 5 Rs?",
            [
                "Dar uma nova função a objetos antigos ou usados." => 10, 
                "Usar itens descartáveis sempre que possível." => 0,
                "Jogar fora produtos eletrônicos assim que quebrarem." => 0,
                "Comprar novos produtos para substituir os antigos." => 0,
                "Comprar produtos que não são recicláveis." => 0,
            ],
        ],
    ];
    
    // Salvar pontuação
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Processa a resposta da pergunta
        if (isset($_POST['pergunta'])) {
            $resposta = $_POST['pergunta'];
            $_SESSION['pontuacaoTotal'] += (int)$resposta; // Adiciona à pontuação total
    
            // Avança para a próxima pergunta
            $_SESSION['perguntaAtual']++;
    
            // Se todas as perguntas foram respondidas, salva a pontuação
            if ($_SESSION['perguntaAtual'] > count($perguntas)) {
                $nome = $_SESSION['nome']; // Recupera o nome da sessão
                $stmt = $conn->prepare("INSERT INTO pontuacoes (nome, pontuacao) VALUES (?, ?)");
                $stmt->bind_param("si", $nome, $_SESSION['pontuacaoTotal']);
                $stmt->execute();
                $stmt->close();
    
                // Marcar que o quiz foi concluído
                $_SESSION['quiz_concluido'] = true; 
    
                // Redireciona para evitar o reenvio do formulário
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
        }
    
        // Processa o nome do usuário
        if (isset($_POST['nome'])) {
            $_SESSION['nome'] = $_POST['nome']; // Armazena o nome na sessão
    
            // Redireciona para evitar o reenvio do formulário
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    
        // Processar comentários
        if (isset($_POST['comentario']) && isset($_POST['token'])) {
            $comentario = $_POST['comentario'];
            $tokenFormulario = $_POST['token'];
    
            // Verifica se o token enviado é válido
            if ($tokenFormulario === $_SESSION['token']) {
                // Adiciona o comentário com a data de criação
                $stmtComentario = $conn->prepare("INSERT INTO comentarios (comentario, nome, data) VALUES (?, ?, NOW())");
                $stmtComentario->bind_param("ss", $comentario, $_SESSION['nome']);
                $stmtComentario->execute();
                $stmtComentario->close();
                
                // Redireciona para a mesma página após o envio do comentário
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } else {
                echo 'Erro: Token inválido!';
            }
        }
    }
    
    // Obter pontuações (top 10 com ordenação por pontuação e nome)
    $result = $conn->query("SELECT * FROM pontuacoes ORDER BY pontuacao DESC LIMIT 10");
    
    // Obter comentários
    $resultComentarios = $conn->query("SELECT * FROM comentarios ORDER BY data DESC LIMIT 5");
    
    ?>
    
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quiz de Sustentabilidade</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
    .comment {
    max-width: 600px; /* Defina a largura máxima desejada */
    word-wrap: break-word; /* Quebra palavras longas */
    overflow-wrap: break-word; /* Quebra palavras longas (para compatibilidade) */
    margin-bottom: 10px; /* Espaçamento entre os comentários */
    padding: 10px; /* Espaçamento interno */
    border: 1px solid #ccc; /* Borda opcional */
    border-radius: 5px; /* Bordas arredondadas */
    background-color: #f9f9f9; /* Cor de fundo opcional */
}        
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    h1 {
        color: #333;
    }
    .quiz-container {
        background: lightgray;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 600px;
        text-align: center;
    }
    label {
        display: block;
        margin: 10px 0;
        text-align: left;
    }
    button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
    }
    button:hover {
        background-color: #218838;
    }
    ul {
        list-style-type: none;
        padding: 0;
    }
    li {
        background: #fff;
        margin: 5px 0;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
    }
    footer {
        margin-top: 20px;
        text-align: center;
    }
    .carousel-item img {
        border-radius: 15px; /* Arredonda as bordas das imagens */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Adiciona sombra às imagens */
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: black; /* Cor de fundo preta para as setas */
        border-radius: 50%; /* Para deixar as setas arredondadas */
    }
    .carousel-control-prev,
    .carousel-control-next {
        filter: invert(1); /* Inverte a cor das setas para que fiquem brancas */
    }
    </style>
</head>
<body>
    <h1>Quiz de Sustentabilidade</h1>

    <div class="quiz-container">
        <?php if (!isset($_SESSION['quiz_concluido'])): ?>
            <form method="post">
                <?php if (isset($_SESSION['nome'])): ?>
                    <h2>Pergunta <?= $_SESSION['perguntaAtual'] ?>:</h2>
                    <p><?= $perguntas[$_SESSION['perguntaAtual']][0] ?></p>
                    <?php foreach ($perguntas[$_SESSION['perguntaAtual']][1] as $opcao => $valor): ?>
                        <label>
                            <input type="radio" name="pergunta" value="<?= $valor ?>" required>
                            <?= $opcao ?>
                        </label>
                    <?php endforeach; ?>
                    <button type="submit">Próxima Pergunta</button>
                <?php else: ?>
                    <label for="nome">Digite seu nome:</label>
                    <input type="text" name="nome" required>
                    <button type="submit">Iniciar Quiz</button>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <h2>Quiz Concluído!</h2>
            <p>Obrigado por participar, <?= $_SESSION['nome'] ?>!</p>
            <p>Sua pontuação total: <?= $_SESSION['pontuacaoTotal'] ?></p>
            <h3>Ranking dos Jogadores</h3>
            <ul>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li><?= htmlspecialchars($row['nome']) ?> - <?= $row['pontuacao'] ?></li>
                <?php endwhile; ?>
            </ul>
            
            <form method="post" action="">
                <button type="submit" name="refazer">Refazer Quiz</button>
            </form>

            <?php
            if (isset($_POST['refazer'])) {
                unset($_SESSION['pontuacaoTotal']);
                unset($_SESSION['perguntaAtual']);
                unset($_SESSION['quiz_concluido']);
                unset($_SESSION['nome']);
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
            ?>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['quiz_concluido'])): ?>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li><?= htmlspecialchars($row['nome']) ?> - <?= $row['pontuacao'] ?></li>
        <?php endwhile; ?>
    </ul>

    <!-- Carrossel de Imagens -->
    <div id="carouselExample" class="carousel slide mt-4" data-ride="carousel"> <!-- Adicionei a classe mt-4 -->
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="7001.png" class="d-block w-100" alt="Imagem 1">
            </div>
            <div class="carousel-item">
                <img src="7002.png" class="d-block w-100" alt="Imagem 2">
            </div>
            <div class="carousel-item">
                <img src="7003.png" class="d-block w-100" alt="Imagem 3">
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExample" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExample" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <h3>Deixe seu comentário:</h3>
    <form method="post">
        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
        <textarea name="comentario" rows="4" required></textarea>
        <button type="submit">Enviar Comentário</button>
    </form>

    <h3>Comentários Recentes:</h3>
    <ul>
        <?php while ($rowComentario = $resultComentarios->fetch_assoc()): ?>
            <li class="comment">
                <strong><?= htmlspecialchars($rowComentario['nome']) ?>:</strong> <?= htmlspecialchars($rowComentario['comentario']) ?>
            </li>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>
    <footer>
        <p>&copy; 2023 Quiz de Sustentabilidade. Todos os direitos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close(); // Fecha a conexão com o banco de dados
?>