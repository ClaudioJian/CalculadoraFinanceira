<?php 
/*+-------------------------------------------------------------------------------------------------+
  |                                                                                                 |
  | Here puts general useful function for database.                                                 |
  | Also is master file that include everything,so you can require this file to get majority funct  |
  |                                                                                                 |
  +-------------------------------------------------------------------------------------------------+
*/


require_once "const.php";

require_once "DB_calculator_financeiro/TB_data.php";
require_once "DB_calculator_financeiro/TB_historico.php";
require_once "AUTH_helper.php";

require_once "ERROR_html.php";

/**
 * connect to database.
 * 
 * auto exit database when error occur.
 * 
 * Not recommended to use this function. Since it don't grab setting value from enviroment values or from server config file.
 * 
 * Also, beware the connection is EXTREMILY dangerous! it log in with adm privileges without password!!!
 * 
 * the link provided give more information about good practice
 * 
 * @link https://www.12factor.net/
 * @return PDO sucess: object pointer to connection of database
 * @return array error: ['sucess'=>DB_ERR_CONNECTION,'description'=>string]
 */
function connect_database(){
    $dns = "mysql:host=" .HOST. ";dbname=".DB_NAME;

    try{
        $conn = new PDO($dns,SERVER_USER);
    }catch(PDOException $e){
        $conn = NULL;
        return ['sucess'=>DB_ERR_CONNECTION,'description'=>$e->getMessage()];
    }
    return $conn;
}



/**
 *  get value posted in json from web in array form.
 *  to use: returned_array['key']
 * @return array ['key'=>'value'] from fetch(url,{...,body:JSON.stringfy(key:value)})
 */
function get_post_values(){
    $request_raw = file_get_contents('php://input');
    $assoc_arr = json_decode($request_raw,true);
    
    return $assoc_arr;
}

?>