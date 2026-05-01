<?php
require_once "../helper/helper.php";

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

if(!$logged) {
    $conn = connect_database();

    // connection error
    if(is_int($conn) && $conn<0) exit; 

    $response = user_can_login($name,$password,$email,$conn);
    // verifica se é logado, int significa que ele não encontrou nenhum usuário
    $logged = is_int($response) ? false : $response['sucess'];
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

<?php if($logged){
    // marcar esse usuário como logado
    $_SESSION['user'] = $name;
     ?>
//retornar html para browser
    <!--informa se o usuário está logado-->
    <header>
        <p> Você já está logado como:</p>
        <!--quem?-->
        <h1><?= htmlspecialchars($name) ?></h1>
        <h2><?= htmlspecialchars($email) ?></h2>
    </header>
<?php }else{ ?>
    <!--informa se o usuário não está logado-->
    <header>
        <p> Você não está logado!</p>
        <!--motivo-->
        <?php 
            // not found por nome
            if(is_int($response)) echo "<p>". $name ." não foi registrado</p>";
            //nome encontrado mas inputs errado
            else {
                //false se o senha errado
                if(!$response['password']) echo "<p>Senha incorreto</p>";
                if(!$response['email']) echo "<p>Email incorreto</p>";
            }
        ?>
    </header>
    <main>
        <form method="POST" action="login.php">
            nome:<input name="nome" type="text" value="<?= htmlspecialchars($name)??'' ?>" required>
            email:<input name="email" type="email" autocomplete="email" value="<?= htmlspecialchars($email)??'' ?>" required>
            senha:<input name="senha" type="password" autocomplete="current-password" value="<?= htmlspecialchars($password)??'' ?>" required>
            <button type="submit">Enviar</button>
        </form>
    </main>

    <a href="cadastrar.php">Cadastrar</a>
<?php
}


?>
    <a href="../index.html">voltar</a>
  </body>
  <script src="auth.js"></script>
</html>