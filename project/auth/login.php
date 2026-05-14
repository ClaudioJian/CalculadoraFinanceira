<?php
require_once "../helper/AUTH_main.php";

session_start();

// normalmente não deve colocar algo como esse no document root
$name = $_POST['nome'] ?? '';
$password = $_POST['senha'] ?? '';
$email = $_POST['email']??''; 

//aqui normalmente filtram dados do POST, sde o dados são prenchido incorretamente, exibe e evite que começe o query para database(se usuário vim aqui primeira vez, o POST é OBRIGATORIADAMENTE de set NULL!)
// o email pode ser no formato incorreto(para filtrar usa filter_var(email,FILTER_VALIDATE_EMAIL))
// para filtrar outros input, filtra por tamanho e tipo, também não esqueça de preocupar do xss ataque

//verifa se já é logado, se não verifica se o input pode ser encontrado no database
$logged = is_logged();


//dummy
$response = NULL; 

if(!$logged && !($email==='' && $password==='' && $name==='')) {
    $conn = connect_database();

    // connection error
    if(is_array($conn) && $conn['sucess']===DB_ERR_CONNECTION) ERR_conn_db();

    $response = user_can_login($name,$password,$email,$conn);
    
    // verifica se é logado, int significa que ele não encontrou nenhum usuário
    $logged = $response['sucess'] <= 0 ? false : true;

    if($logged) {
        $record = $response['result'];
        log_in($record);
    }
}

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
<?php if($logged){?>
    <!--informa se o usuário está logado-->
    <header>
        <?php //segunda vez de entrar essa página e está logado(não tem nenhum data postado)
        if($email==='' && $name==='' && $password==='') {?>
            <p> Você já está logado como:</p>
        <?php }else{ ?>
            <p> Você está logado como:
        <?php } ?>
        <!--informação do login?-->
        <h1><?= htmlspecialchars($_SESSION['user']) ?></h1>
        <h2><?= htmlspecialchars($_SESSION['email']) ?></h2>

    </header>

    <footer class="borda">
        <!--deslogar-->
        <a href="deslogin.php">deslogin</a>
        <a href="delete_user.php">deletar seu usuário</a>
<?php }else{ ?>
    <!--informa se o usuário não está logado-->
    <header>
        <p style="top: 5px;left:10px; display:inline;"> Você não está logado!</p>
        <!--motivo-->
        <?php
            // not found por nome
            if($response !== NULL && !($email==='' && $password==='' && $name==='')){
                if($response['sucess'] === USER_NOT_FIND ) echo "<p>". $name ." não foi registrado</p>";
                //nome encontrado mas inputs errado
                else if($response['sucess'] === USER_WRONG_CREDIT){
                    //false se o senha errado
                    if($response['password']){ echo "<p>Senha incorreto</p>";}
                    if($response['email']) {echo "<p>Email incorreto</p>";}
                }
            }
        ?>
    </header>
    <main>
        <form method="POST" action="login.php" style="top: 70px;left:10px;">
            <br>
            nome:<input name="nome" type="text" value="<?= htmlspecialchars($name)??'' ?>" required><br>
            email:<input name="email" type="email" autocomplete="email" value="<?= htmlspecialchars($email)??'' ?>" required><br>
            senha:<input name="senha" type="password" autocomplete="current-password" value="<?= htmlspecialchars($password)??'' ?>" required>
            <button type="submit" class="b2">Enviar</button>
        </form>
    </main>
        <footer class="borda">
            <a href="cadastrar.php">Cadastrar</a>  
    
<?php
}
?>
            <a href="../index.html">Voltar</a>
        </footer>
    </div>
  </body>
  <script src="auth.js"></script>
</html>