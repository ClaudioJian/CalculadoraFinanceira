<?php 
/*+-------------------------------------------------------------------------------------------------+
  |                                                                                                 |
  | Here puts function that communicate to table data.                                              |
  |                                                                                                 |
  +-------------------------------------------------------------------------------------------------+
*/

/**
 * retrieve all data of table data for that user
 * @param PDO $conn Database connection
 * @return object where contain all result of query
 */
function retrieve_user_data(string $user_name,PDO $conn){
    //search row with same name
    $query = "SELECT * FROM data WHERE name=:user_name";
    //query
    $smtm = $conn->prepare($query);
    $smtm->bindParam(':user_name',$user_name);
    $smtm->execute();

    //the result will be in obj
    $result = $smtm->fetch(PDO::FETCH_OBJ);
    return $result;
}



/**
 * check if user can login.
 * 
 * this function do query to find user. 
 * auto disconnect if user not finded in db.
 * 
 * @param PDO $conn Database connection
 * 
 * @return object row of user in format: {column_name=value} 
 * @return array
 * - ['sucess'=>11,'result'=>object row finded,'db'=>$conn] if all crendential matches
 * - ['sucess'=>USER_WRONG_CREDIT,'password'=>bool,'email'=>bool] if user exist but data wrong, the bool indicate true if that data wrong
 * - ['sucess'=>USER_NOT_FIND,'description'=>string] user not finded in database
 */
function user_can_login(string $user_name,string $password,string $email,PDO $conn){
    $result = retrieve_user_data($user_name,$conn);
    
    if($result){
        $err = ['password'=>false,'email'=>false];
        if($result->senha != $password) $err['password'] = true;
        if($result->email != $email) $err['email'] = true;
        //return immedialy if there has different value
        foreach($err as $e){
            if($e) {
                $err['sucess']=USER_WRONG_CREDIT;
                return $err;
                }
        }
        return ['sucess'=>11,'result'=>$result,'DB'=>$conn];
    }
    $conn = NULL;
    return ['sucess'=>USER_NOT_FIND, "description"=>'user not find'];
}

/**
 * find if user name is alredy taken
 * @param PDO $conn Database connection.
 * @return bool var
 * - **true**
 * - **false** : new user.
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
 * @return object where contain all result of query
 * @return array
 * - sucess: ['sucess'=>DB_INSERT,'result'=>obj row/record finded] when sign up sucess
 * - ['sucess'=>USER_NOT_LOGGED,'description'=>'string'] if not logged
 * - ['sucess'=>USER_ALREDY_EXIST,'description'=>string] if user alredy exist in database
 * - ['sucess'=>DB_ERR_INSERT,'description'=>string] if cannot create in database
 */
function register_user(string $user_name,string $password,string $email,PDO $conn){
    try{
        //find if user name alredy taken
        if(user_alredy_exist($user_name,$conn)) return ['sucess'=>USER_ALREDY_EXIST,'description'=>'user alredy exist'];

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
        return ['sucess'=>DB_ERR_INSERT,'description'=>$e->getMessage()];
    }

    //return entire row of that user, to set information into session
    $result = retrieve_user_data($user_name,$conn);

    $conn = NULL;

    return ['sucess'=>52,'result'=>$result];
}


/**
 * delete user from database
 * 
 * auto disconnect from database
 * @param PDO $conn PDO object pointer to connection of database, can be finded by return value of connect_database()
 * @return array
 * - sucess: ['sucess'=>DB_DELETE,'result'=>obj row/record finded] when deleted
 * - ['sucess'=>USER_NOT_LOGGED,'description'=>'string'] if not logged
 * - ['sucess'=>USER_NOT_FIND,'description'=>'string'] if user not existed
 * - ['sucess'=>DB_ERR_DELETE,'description'=>description] database fail to delete this user
 */
function delete_user(PDO $conn){
    if(!is_logged()) return ['sucess'=>USER_NOT_LOGGED,'description'=>'not logged'];
    if(!user_alredy_exist($_SESSION['user'],$conn)) return ['sucess'=>USER_NOT_FIND,'description'=>'usuário não existe'];
    try{
        //search row with this user
        $query = "DELETE FROM data WHERE user_id=:user_id";
        //query
        $smtm = $conn->prepare($query);
        $smtm->bindParam(':user_id',$_SESSION['user_id']);
        if(!($smtm->execute())) throw new Exception("Database não consegue deletar o usuário!");
    }catch(Exception $e){
        $conn = NULL;
        return ['sucess'=>DB_ERR_DELETE,'description'=>$e->getMessage()];
    }

    $conn = NULL;

    return ['sucess'=>DB_DELETE,'description'=>'user deleted'];
}

?>