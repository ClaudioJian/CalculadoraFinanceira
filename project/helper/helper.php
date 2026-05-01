<?php 
require_once "const.php";
require_once "ERROR_html.php";


/**
 * check if user logged
 * @return 1 if user is logged else 0
 */
function is_logged(){
    if(isset($_SESSION['user'])) return 1;
    else return 0;
}

/**
 * connect to database.
 * 
 * Not recommended to use this function. Since it don't grab setting value from enviroment values or from server config file.
 * 
 * Also, beware the connection is EXTREMILY dangerous! it log in with adm privileges without password!!!
 * 
 * the link provided give more information about good practice
 * 
 * @link https://www.12factor.net/
 * @return PDO PDO pointer to connection if sucess
 * @return -1 if error occurs
 * @throws error if connection failed
 */
function connect_database(){
    $dns = "mysql:host=" .HOST. ";dbname=".DB_NAME;

    try{
        $conn = new PDO($dns,SERVER_USER);
    }catch(PDOException $e){
        $conn = NULL;
        echo "ERROR: ".$e->getMessage();
        return -1;
    }
    return $conn;
}

/**
 * check if user can login.
 * 
 * this function do query to find user. 
 * auto disconnect if user not finded in db.
 * 
 * @param PDO $conn PDO object pointer to connection of database, can be finded by return value of connect_database()
 * @return object row of user in format: {column_name=value} 
 * @return array if [0] or ['sucess'] => false; then: user name in databese but others isn't.
 * [1] or ['password'], [2] or ['email']; value: boolean true for that key if problem is that else false
 * @return array if [0] or ['sucess'] => true; then: [1] or ['result'] =>row finded; [2] or ['db']=> PDO pointer to connection
 * @return false not find
 */
function user_can_login(string $user_name,string $password,string $email,PDO $conn){
    //search row with same name
    $query = "SELECT * FROM data WHERE name=:user_name";
    //query
    $smtm = $conn->prepare($query);
    $smtm->bindParam(':user_name',$user_name);
    $smtm->execute();

    //the result will be in obj
    $result = $smtm->fetch(PDO::FETCH_OBJ);
    if($result){
        $err = ['sucess'=>false,'password'=>false,'email'=>false];
        if($result->senha != $password) $err['password'] = true;
        if($result->email != $email) $err['email'] = true;
        //return immedialy if there has different value
        foreach($err as $e){
            if($e) return $err;
        }
        return ['sucess'=>true,'result'=>$result,'DB'=>$conn];
    }
    $conn = NULL;
    return 0;
}

/**
 * find if user name is alredy taken
 * @param PDO $conn PDO object pointer to connection of database, can be finded by return value of connect_database()
 * @return true alredy taken
 * @return false not taken
 */
function user_alredy_exist(string $user_name,PDO $conn){
    $query = "SELECT 1 FROM data WHERE name=:user_name";
    $smtm = $conn->prepare($query);
    $smtm->bindParam(':user_name',$user_name);
    $smtm->execute();

    return $smtm->rowCount()>0;
}


/**
 * register user in database
 * auto disconnect from database
 * 
 * @param PDO $conn PDO object pointer to connection of database, can be finded by return value of connect_database()
 * @return 1 sucess
 * @return 0 when alredy taken
 * @return -1 if error occurs
 * @throws error usuário não consegue registrar no database
 */
function register_user(string $user_name,string $password,string $email,PDO $conn){
    try{
        //find if user name alredy taken
        if(user_alredy_exist($user_name,$conn)) return 0;

        //search row with same name
        $query = "INSERT INTO data(name,senha,email) VALUES(:user_name,:password,:email)";
        //query
        $smtm = $conn->prepare($query);
        $smtm->bindParam(':user_name',$user_name);
        $smtm->bindParam(':password',$password);
        $smtm->bindParam(':email',$email);
        if(!($smtm->execute())) throw new Exception("Usuário não foi criado!");
    }catch(Exception $e){
        $conn = NULL;
        echo "ERROR: ".$e->getMessage();
        return -1;
    }

    $conn = NULL;
    return 1;
}

?>