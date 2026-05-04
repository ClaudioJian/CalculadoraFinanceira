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


//se não está logado e todos valores não são nulos
if(!$logged && !($email==='' && $password==='' && $name==='')) {
    $conn = connect_database();

    // connection error
    if(is_array($conn) && $conn['sucess']===DB_ERR_CONNECTION) ERR_conn_db();

    $response = register_user($name,$password,$email,$conn);

    $state = $response['sucess'];

    if($state<=0){
        if($state===USER_ALREDY_EXIST) {
                $logged = false;
                ERR_user_alredy_exist($_POST);
        }else if($state===DB_ERR_INSERT){
            //error html
            $logged = false;
            ERR_registration($_POST);
        }
    }
    else{
        $logged = true;
        log_in($response['result']);
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

<?php 
//se não está logado e todos valores são nulos
if(!$logged && ($email==='' && $password==='' && $name==='')){ ?>
    <!--informa se o usuário não está logado-->
    <header>
        <p> Cadastrar-se:</p>
    </header>
    <main>
        <form method="POST" action="cadastrar.php">
            nome:<input name="nome" type="text" value="<?= htmlspecialchars($name)??'' ?>" required>
            email:<input name="email" type="email" autocomplete="email" value="<?= htmlspecialchars($email)??'' ?>" required>
            senha:<input name="senha" type="password" autocomplete="current-password" value="<?= htmlspecialchars($password)??'' ?>" required>
            <button type="submit">Enviar</button>
        </form>
    </main>

    <footer>
        <a href="login.php">login</a>
<?php }
// se é logado e o valor é nulo, significa que vem de outro página
else{
    if($logged && ($email==='' && $password==='' && $name==='')){?>
    <!--informa se o usuário está logado-->
    <header>
        <p> Você já está logado como:</p>
        <!--quem?-->
        <h1><?= htmlspecialchars($name) ?></h1>
        <h2><?= htmlspecialchars($email) ?></h2>

    </header>
    <?php }else{ ?>
    <header>
        <p> Cadastrado com sucesso!</p>
    </header>
<?php } ?>
    <footer>
        <!--deslogar-->
        <a href="deslogin.php">deslogin</a>
        <h1>Pergunte se usuário realmente quer deletar antes de entrar o link!!!</h1>
        <a href="delete_user.php">deletar seu usuário</a>
<?php } ?>  
        <a href="../index.html">voltar</a>
    </footer>
  </body>
  <script src="auth.js"></script>
</html>