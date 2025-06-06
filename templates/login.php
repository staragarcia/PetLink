<?php declare(strict_types = 1); ?>

<!-- <link rel="stylesheet" href="../stylesheets/style.css"> -->


<?php function drawLogin($csrf_token): void { ?>
  <?php if (isset($_GET['success'])): ?>
  <div id="success-message" class="success-message" style="margin-bottom: 0;">Alterações guardadas com sucesso.</div>
  <?php endif; ?>
  <section class="form-container login">
    <h2>Login</h2>
    <h3>Bem-vindo de volta!</h3>
    <?php if (isset($_GET['message']) && !empty($_GET['message'])): ?>
      <p class="error-message"><?= htmlspecialchars($_GET['message']) ?></p>
    <?php elseif (isset($_GET['error'])): ?>
      <p class="error-message">Credenciais inválidas. Por favor, tente novamente.</p>
    <?php endif; ?>
    <form action="../actions/action_login.php" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>">
      <label for="username">Username ou email</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Login</button>
    </form>
    <p>Ainda não se juntou à Petlink? <a href="signup.php">Crie uma conta</a>!</p>
    <p><a href="retrievePassword.php">Esqueci minha senha</a></p>
  </section>
<?php } ?>
