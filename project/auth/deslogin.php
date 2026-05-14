<?php
require_once "../helper/AUTH_main.php";

session_start();

//verifa se já é logado
$logged = is_logged();

?>
<!--header-->

<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>*Nome da empresa*</title>
    <link rel="stylesheet" href="../esqueleto.css"/>
  </head>

  <body>
    <div class="box">
<?php if($logged){ 
    //guardar informação se presica exibir
    $user = $_SESSION['user'];
    $email = $_SESSION['email'];
    deslogin(); 
    ?>
    <!--informa se o usuário saiu da conta-->
    <header>
        <p> Você saiu da conta!</p>
    </header>
<?php }else{ ?>
    <!--usuário entra esse página, mas não precisava de deslogar pois nunca logou-->
    <header>
        <p> Você não está logado!</p>
    </header>
<?php
}
?>
        <footer>
            <div class="b4">
                <a href="cadastrar.php">Cadastrar</a>
                <a href="login.php">Login</a>
                <a href="../index.html">voltar</a>
            </div>
        </footer>
    </div>
  </body>
  <script src="auth.js"></script>
</html>