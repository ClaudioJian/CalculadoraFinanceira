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

<?php if($logged){ 
    //guardar informação se presica exibir
    $user = $_SESSION['user'];
    $email = $_SESSION['email'];

    $conn = connect_database();
    // connection error
    if(is_array($conn) && $conn['sucess']===DB_ERR_CONNECTION) ERR_conn_db();

    $response = delete_user($conn);
    $code = $response['sucess'];
    if($code<=0){
        if($code === USER_NOT_FIND){ ?>
            <!--informa se o usuário não existe-->
            <header>
                <p> Você deletou a conta da conta!</p>
            </header>
        <?php }else if($code === DB_ERR_DELETE){ ?>
            <!--informa se o databease não conseguiu deletar ele-->
            <header>
                <p> Database não conseguiu deletar você!</p>
            </header>
        <?php }
    }else{
        deslogin(); 
    ?>
        <!--informa se o usuário saiu da conta-->
        <header>
            <p> Você deletou a conta da conta!</p>
        </header>
    <?php } ?>
<?php }else{ ?>
    <!--usuário entra esse página, mas não precisava de deslogar pois nunca logou-->
    <header>
        <p> Você não está logado!</p>
    </header>
<?php
}
?>
    <footer>
        <a href="cadastrar.php">Cadastrar</a>
        <a href="login.php">Login</a>
        <a href="../index.html">voltar</a>
    </footer>
  </body>
  <script src="auth.js"></script>
</html>