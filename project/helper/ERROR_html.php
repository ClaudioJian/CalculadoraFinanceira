<?php 
/*+-----------------------------------------------+
  |                                               |
  | Here puts function that show error menssage   |
  | for user know error                           |
  |                                               |
  +-----------------------------------------------+
*/

/**
 * echo html for this error(fail to register user info, user alredy exist)
 * 
 * exit in end of function
 * 
 * @param array $input contain all post information directly 
 * 
 * PLEASE CHANGE THIS CODE!!!
 */
function ERR_user_alredy_exist($input){
    echo "Já existe";
    exit();
}

/**
 * echo html for this error(fail to register user info, permission error or something else)
 * 
 * exit in end of function
 * 
 * @param array $input contain all post information directly 
 * 
 * PLEASE CHANGE THIS CODE!!!
 */
function ERR_registration($input){
    echo "falhou em registrar, tente novamente";
    exit();
}

/**
 * echo html for this error(fail to connect database)
 * 
 * exit in end of function.
 * 
 * PLEASE CHANGE THIS CODE!!!
 */
function ERR_conn_db(){
    echo "Ocorreu um erro. Tente novamente mais tarde.";
    exit();
}

?>
