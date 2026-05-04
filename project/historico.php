<?php 
/*+-------------------------------------------------------------------------------------------------+
  |                                                                                                 |
  | This file are meant to be fetched by fetch api in javascript                                    |
  | Return in json format                                                                           |
  |                                                                                                 |
  | result.sucess: true or <=0.  Check this value to see if request was sucess                      |
  | if result.sucess <=0: -1 user not logged, 0 not finded in database.                             |
  | result.description: only apper when has error, descripe what error is                           |
  |                                                                                                 |
  | when retrieving data(receive request with method POST),                                         |
  | if sucess create json that can be acess by:                                                     |
  | result[n]: where n is int and indicate as index of each row/record finded.                      |
  | result[n].variableName to acess value                                                           |
  |                                                                                                 |
  | When inserting data(receive request with method GET) return state of request                    |
  |                                                                                                 |
  +-------------------------------------------------------------------------------------------------+
*/

require_once "./helper/AUTH_main.php";
session_start();

// que tipo vai ser retornado?
header("content-type:application/json");

$conn = connect_database();

if(is_array($conn) && $conn['sucess']===DB_ERR_CONNECTION) {
    echo json_encode($conn);
    exit();
}


$method = $_SERVER['REQUEST_METHOD'];

if($method == 'GET'){
    $response = retrieve_graph_data($conn);
    $status = $response['sucess'];

    //diconnecting
    $conn = NULL;

    echo json_encode($response);
    exit();
}else if($method == 'POST'){
    $inputs = get_post_values();
    $code = $inputs['code'];

    if($code === DB_INSERT){
        $response = insert_historico($conn,$inputs['data']);

        //diconnecting
        $conn = NULL;

        echo json_encode($response);
        exit();
    }
    else if($code === DB_DELETE){
        $response = delete_record($conn,$inputs['data']);

        //diconnecting
        $conn = NULL;

        echo json_encode($response);
        exit();
    }
}else{
    //invalid request
    //diconnecting
    $conn = NULL;
    echo json_encode(['sucess'=>-100,'description'=>'invalid request']);
    exit();
}
?>