<?php
session_start();
session_destroy(); // Destrói a sessão (faz o logout)
header("Location: spotfinder.php"); // Manda o utilizador de volta para a página inicial
exit;
?>