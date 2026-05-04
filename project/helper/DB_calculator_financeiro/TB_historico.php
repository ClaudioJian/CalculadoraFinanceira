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
 * - sucess: ['sucess'=>DB_SELECT,'int n'=>obj row/records,'description'=>string] give array of obj where contain all result of query
 * - ['sucess'=>USER_NOT_LOGGED,'description'=>string] when not logged
 * - ['sucess'=>USER_NOT_FIND,'description'=>string] if no find
 * - ['sucess'=>DB_ERR_SELECT,'description'=>string] if some error happens when doing query
 */
function retrieve_graph_data(PDO $conn){
    if(!is_logged()) return ['sucess'=>USER_NOT_LOGGED,'description'=>'not logged'];

    $query = "SELECT 
    record_id,tipo,investimento,valor_a_ser_investido,prazo,investimento_seguinte,percentual_crescimento
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
        $result['sucess'] = DB_SELECT;
        $result['description'] = "data finded";

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
 * - sucess: ['sucess'=>DB_INSERT,'description'=>string]
 * - ['sucess'=>USER_NOT_LOGGED,'description'=>string] when not logged
 * - ['sucess'=>DB_ERR_INSERT,'description'=>string] when cannot register
 * 
 */
function insert_historico(PDO $conn,array $inputs){
    if(!is_logged()) return ['sucess'=>USER_NOT_LOGGED,'description'=>'not logged'];

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
        return ['sucess'=>DB_ERR_INSERT,'description'=>$e->getMessage()];
    }
    return ['sucess'=>DB_INSERT,'description'=>'data saved'];
}


/**
 * delete row/record indicated by user.
 * 
 * find the row by user id and record id indicated in $arr_ids.
 * need be logged to delete.
 * @param array $arr_ids contain all record id for delete
 * @param PDO $conn PDO object pointer to connection of database, can be finded by return value of connect_database()
 * @return array 
 * - sucess: ['sucess'=>DB_DELETE,'description'=>string,'affected_rows'=> int number of deleted rows]
 * - ['sucess'=>USER_NOT_LOGGED,'description'=>string] when not logged
 * - ['sucess'=>DB_ERR_DELETE,'description'=>string] when database cannot delete row
 * - ['sucess'=>DATA_NOT_FOUND,'description'=>string] when query sucess but no row deleted
 */
function delete_record(PDO $conn, array $arr_ids){
    if(!is_logged()) return ['sucess'=>USER_NOT_LOGGED,'description'=>'not logged'];

    try{
        //create safe variable name
        $buffer = [];
        for($i = 0; $i<count($arr_ids);$i++){
            array_push($buffer,":v".(string)$i);
        }
        $all_id = join(',',$buffer);

        //search row with same name
        $query = "
        DELETE FROM historico
        WHERE
        user_id = :user_id AND
        record_id IN (".$all_id.")";

        //query
        $smtm = $conn->prepare($query);

        //bind parameters
        $smtm->bindParam(':user_id',$_SESSION['user_id']);

        for($i = 0; $i<count($arr_ids);$i++){
            $smtm->bindParam(':v'.$i,$arr_ids[$i]);
        }

        if(!($smtm->execute())) throw new Exception("Não consegue deletar os dados histórico do usuário!");
        if($smtm->rowCount()<=0) return ['sucess'=>DATA_NOT_FOUND,'description'=>'no data can be deleted'];
    }catch(Exception $e){
        $conn = NULL;
        return ['sucess'=>DB_ERR_DELETE,'description'=>$e->getMessage()];
    }
    return ['sucess'=>DB_DELETE,'description'=>'data deleted','affected_rows'=>$smtm->rowCount()];
}
?>