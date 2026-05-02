<?php 
/*+-------------------------------------------------------------------------------------------------+
  |                                                                                                 |
  | Here puts function that communicate to table historico.                                         |
  |                                                                                                 |
  +-------------------------------------------------------------------------------------------------+
*/


/**
 * grabs all data for construct graph again.
 * 
 * this function returns all row possible created by user.
 * 
 * you can check if is query sucess by check return_value['sucess']===true.
 * 
 * if sucess full use result[n]->data to grab data.(n is order).
 * 
 * auto disconnect from database when error occurs.
 * 
 * Please disconnect from database if you aren't use it any more.
 * @param PDO $conn PDO object pointer to connection of database, can be finded by return value of connect_database()
 * @return array 
 * - sucess: ['sucess'=>55,'int n'=>obj row/records] give array of obj where contain all result of query
 * - ['sucess'=>USER_NOT_LOGGED,'description'=>string] when not logged
 * - ['sucess'=>USER_NOT_FIND,'description'=>string] if no find
 * - ['sucess'=>DB_ERR_SELECT,'description'=>string] if some error happens when doing query
 */
function retrieve_graph_data(PDO $conn){
    if(!is_logged()) return ['sucess'=>USER_NOT_LOGGED,'description'=>'not logged'];

    $query = "SELECT 
    tipo,investimento,valor_a_ser_investido,prazo,investimento_seguinte,percentual_crescimento
    FROM historico 
    WHERE user_id=:user_id
    ";

    //query
    try{
        $smtm = $conn->prepare($query);
        $smtm->bindParam(':user_id',$_SESSION['user_id']);
        $smtm->execute();


        if($smtm->rowCount()<1) return ['sucess'=>USER_NOT_FIND,'description'=>'no record find'];
        //the result will be in obj
        $result = $smtm->fetchAll(PDO::FETCH_OBJ);
        $result['sucess'] = 55;

        return $result;
    }catch(PDOException $e){
        $conn = NULL;
        return ['sucess'=>DB_ERR_SELECT,'description'=>$e->getMessage()];
    }
}

/**
 * insert data to database by using POST array
 * 
 * auto disconnect from database when error occur.
 * Please disconnect from database if you aren't use it any more.
 * @param PDO $conn PDO object pointer to connection of database, can be finded by return value of connect_database()
 * @return array 
 * - sucess: ['sucess'=>52]
 * - ['sucess'=>USER_NOT_LOGGED,'description'=>string] when not logged
 * - ['sucess'=>DB_ERR_INSERT,'description'=>string] when cannot register
 * 
 */
function insert_historico(PDO $conn){
    if(!is_logged()) return ['sucess'=>USER_NOT_LOGGED,'description'=>'not logged'];
    $inputs = get_post_values();

    try{

        //search row with same name
        $query = "
        INSERT INTO historico
        (user_id,tipo,investimento,valor_a_ser_investido,prazo,investimento_seguinte,percentual_crescimento)
        VALUES
        (:id,:type,:inv,:vi,:p,:invs,:pc)
        ";


        //query
        $smtm = $conn->prepare($query);

        //bind parameters
        $smtm->bindParam(':id',$_SESSION['user_id']);
        $smtm->bindParam(':type',$inputs['tipo']);
        $smtm->bindParam(':inv',$inputs['investimento']);
        $smtm->bindParam(':vi',$inputs['valor_a_ser_investido']);
        $smtm->bindParam(':p',$inputs['prazo']);
        $smtm->bindParam(':invs',$inputs['investimento_seguinte']);
        $smtm->bindParam(':pc',$inputs['percentual_crescimento']);

        if(!($smtm->execute())) throw new Exception("Não consegue inserir esses dados!");
    }catch(Exception $e){
        $conn = NULL;
        return ['sucess'=>DB_ERR_INSERT,'description'=>$e->getMessage(),'post'=>$inputs];
    }
    return ['sucess'=>52];
}
?>