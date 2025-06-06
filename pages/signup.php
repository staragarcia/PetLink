<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../templates/layout.php');
  require_once(__DIR__ . '/../templates/signup.php');
  require_once(__DIR__ . '/../utils/security.php');
  require_once(__DIR__ . '/../init.php');

  $csrf_token = generate_csrf_token();
  drawHeader();
  drawSignUp($csrf_token);
  drawFooter();
?>
