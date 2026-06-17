<?php
session_start();
require_once 'conexao.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao == 'registar') {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $erro = "Este e-mail já se encontra registado!";
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$nome, $email, $hash_password])) {
                $sucesso = "Conta criada com sucesso! Já podes fazer login.";
            } else {
                $erro = "Ocorreu um erro ao criar a conta.";
            }
        }
    } elseif ($acao == 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id, nome, password FROM utilizadores WHERE email = ?");
        $stmt->execute([$email]);
        $utilizador = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilizador && password_verify($password, $utilizador['password'])) {
            $_SESSION['utilizador_id'] = $utilizador['id'];
            $_SESSION['utilizador_nome'] = $utilizador['nome'];
            header("Location: spotfinder.php");
            exit;
        } else {
            $erro = "E-mail ou palavra-passe incorretos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SpotFinder &ndash; Entrar</title>
<link rel="stylesheet" href="css.css">
</head>
<body class="login-page">

<div class="login-container">
  <div class="login-sidebar" style="background-image: linear-gradient(rgba(17, 17, 17, 0.4), #111111), url('Miradouro-da-Graca-Best-Sunset-Viewpoint-in-Lisboa-Portugal-View-of-Sao-Jorge-Castle.jpg');">
    <div class="sidebar-content">
      <a href="spotfinder.php" class="login-logo">
        SpotFinder
      </a>
      <p>Descobre e faz a gestão dos melhores eventos em Portugal, desde festivais a concertos exclusivos.</p>
    </div>
  </div>

  <div class="login-form-area">
    <div class="form-box">
      
      <?php if ($erro): ?>
          <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
              <?= htmlspecialchars($erro) ?>
          </div>
      <?php endif; ?>
      
      <?php if ($sucesso): ?>
          <div style="background: #dbeafe; color: #3b82f6; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600;">
              <?= htmlspecialchars($sucesso) ?>
          </div>
      <?php endif; ?>

      <div id="bloco-login">
        <h2>Bem-vindo de volta</h2>
        <p class="subtitle">Introduza as suas credenciais para aceder à sua conta</p>

        <form action="spotfinder_auth.php" method="POST">
          <input type="hidden" name="acao" value="login">
          
          <div class="input-group">
            <label for="login-email">E-mail</label>
            <input type="email" id="login-email" name="email" placeholder="exemplo@email.com" required>
          </div>

          <div class="input-group">
            <div class="label-row">
              <label for="login-password">Palavra-passe</label>
              <a href="#" class="forgot-link">Esqueceu-se da palavra-passe?</a>
            </div>
            <input type="password" id="login-password" name="password" placeholder="••••••••" required>
          </div>

          <div class="remember-me">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Lembrar-me neste dispositivo</label>
          </div>

          <button type="submit" class="btn-login">Entrar</button>
        </form>

        <div class="form-footer">
          Não tem uma conta? <a href="#" onclick="toggleForms(event, 'registo')">Criar conta</a>
        </div>
      </div>

      <div id="bloco-registo" style="display: none;">
        <h2>Criar Conta</h2>
        <p class="subtitle">Junte-se a nós para descobrir os melhores spots</p>

        <form action="spotfinder_auth.php" method="POST">
          <input type="hidden" name="acao" value="registar">
          
          <div class="input-group">
            <label for="reg-nome">Nome Completo</label>
            <input type="text" id="reg-nome" name="nome" placeholder="O seu nome" required>
          </div>

          <div class="input-group">
            <label for="reg-email">E-mail</label>
            <input type="email" id="reg-email" name="email" placeholder="exemplo@email.com" required>
          </div>

          <div class="input-group">
            <label for="reg-password">Palavra-passe</label>
            <input type="password" id="reg-password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
          </div>

          <button type="submit" class="btn-login" style="margin-top: 10px;">Registar e Criar Conta</button>
        </form>

        <div class="form-footer">
          Já tem uma conta? <a href="#" onclick="toggleForms(event, 'login')">Fazer Login</a>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  function toggleForms(e, tipo) {
    e.preventDefault(); 
    if (tipo === 'registo') {
      document.getElementById('bloco-login').style.display = 'none';
      document.getElementById('bloco-registo').style.display = 'block';
    } else {
      document.getElementById('bloco-registo').style.display = 'none';
      document.getElementById('bloco-login').style.display = 'block';
    }
  }
</script>

</body>
</html>