<?php 

/*+-----------------------------------------------+
  |                                               |
  | Here puts function manage state of browser    |
  |                                               |
  +-----------------------------------------------+
*/

/**
 * check if user logged
 * @return 1 if user is logged else 0
 * 
 */
function is_logged(){
    if(isset($_SESSION['user'])) return 1;
    else return 0;
}


/**
 * Use record/row finded to store information
 * @param mixed $record store the row finded
 */
function log_in($record){
    $_SESSION['user'] = $record->name;
    $_SESSION['email'] = $record->email;
    $_SESSION['user_id'] = $record->user_id;
}


function deslogin(){
    unset($_SESSION['user']);
    unset($_SESSION['email']);
    unset($_SESSION['user_id']);
}

?>