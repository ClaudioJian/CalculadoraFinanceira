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
 * - ['sucess'=>DATA_NOT_FOUND,'description'=>string] if no find
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


        if($smtm->rowCount()<1) return ['sucess'=>DATA_NOT_FOUND,'description'=>'no record find'];
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
 * filter record id array passed and return array filtered with only existing array
 * @param PDO $conn Database connection.
 * @return array 
 * contain only records filtered and sucess state
 * - if no record find for this user: ['sucess'=>DB_ERR_UPDATE,'description'=>"user don't have any data registered"]
 * 
 * - if user has record but record id passed don't exist: ['sucess'=>DB_ERR_UPDATE,'description'=>"record id passed don't exist for this user"];
 * 
 * - error when selecting/executing
 */
function filter_record(array $input,PDO $conn){
    try{
        $query = "SELECT record_id FROM historico WHERE user_id=:uid";
        $smtm = $conn->prepare($query);
        $smtm->bindParam(':uid',$_SESSION['user_id']);
        $smtm->execute();

        if($smtm->rowCount()<=0) return ['sucess'=>DATA_NOT_FOUND,'description'=>"user don't have any data registered"];
        //add to buffer only data valid
        $buffer = [];
        $exist=0;
        while($result = $smtm->fetch(PDO::FETCH_OBJ)){
            if(isset($input[$result->record_id])) {
                $buffer[$result->record_id] = $input[$result->record_id];
                $exist = 1;
            }
        }

        if(!$exist) return ['sucess'=>REQUEST_INVALID_INPUT,'description'=>"record id passed don't exist for this user"];
        else {
            $buffer['sucess']= DB_SELECT;
            $buffer['description'] = 'record id exists';
            return $buffer;
        }
    }catch(PDOException $e){
        $conn = NULL;
        return ['sucess'=>DB_ERR_SELECT,'description'=>$e->getMessage()];
    }
}




/**
 * bind all non null input to param smtm
 * 
 * It will bind the param with input that have same key name as field name
 * 
 * Example: tipo=:tipo
 * @param array $field contain all input key name send by front-end
 */
function bind_avaible_param(array $inputs,array $field,PDOStatement $smtm){
    $buf = [];
    foreach($field as $f){
        // example tipo=:tipo
        if(isset($inputs[$f])) $smtm->bindValue(':'.$f, $inputs[$f]);
        $buf[] = ":".$f." && ". $inputs[$f];
    }
    return $buf;
}

/**
 * prefix ":" to all value(non associative array) and separate with ","
 * 
 * @param array $field list of all input name(post)
 * @param int $mode 0 default, NAMED_PARAM_EQUI turn to (field_name=:field_name)
 * @return string
 * 
 * - named param with same value name in field
 */
function named_parameter(array $field,array $inputs,int $mode = 0){
    $buffer = [];
    foreach($field as $f){
        $prefix = $mode === NAMED_PARAM_EQUI ? $f."=" : "";
        if(isset($inputs[$f])) $buffer [] = $prefix.':'.$f;
    }
    return join(', ',$buffer);
}


/**
 * receive data like [record_id=>[data1=>data2],record_id2=>[...]] and update all record of logged user
 */
function update_record(PDO $conn,array $inputs){
    if(!is_logged()) return ['sucess'=>USER_NOT_LOGGED,'description'=>'not logged'];
    

    try{
        $data = filter_record($inputs,$conn);

        if($data['sucess']<=0) {
            return $data;
        }

        $conn->beginTransaction();

        //list all possible data from front-end
        $allowed_field =['tipo','investimento','valor_a_ser_investido','prazo','investimento_seguinte','percentual_crescimento'];
        $affected_rows = 0;

        foreach($data as $rid=>$fields){
            $fields =(array)$fields;
            $all_updates = named_parameter($allowed_field,$fields,NAMED_PARAM_EQUI);
            
            // no update
            if(empty($all_updates)) continue;

            $query = "UPDATE historico SET ".$all_updates." WHERE user_id=:user_id AND record_id=:record_id";
            
            $smtm = $conn->prepare($query);

            $smtm->bindValue(':record_id',$rid,PDO::PARAM_INT);
            $smtm->bindValue(':user_id',$_SESSION['user_id'],PDO::PARAM_INT);

            bind_avaible_param($fields,$allowed_field,$smtm);

            if(!($smtm->execute())) throw new Exception("Não consegue atualizar esses dados!");

            $affected_rows += $smtm->rowCount();
        }
        $conn->commit();
        //retrive inserted id
        return ['sucess'=> DB_UPDATE,'description' => 'data updated','affected_rows'=> $affected_rows];
    }catch(Exception $e){
        $conn->rollBack();
        $conn = NULL;
        return ['sucess'=>DB_ERR_UPDATE,'description'=>$e->getMessage()];
    }

}


/**
 * insert data to database by using POST array
 * 
 * auto disconnect from database when error occur.
 * Please disconnect from database if you aren't use it any more.
 * @param PDO $conn PDO object pointer to connection of database, can be finded by return value of connect_database()
 * @return array 
 * - sucess: ['record_id'=>int,'sucess'=>DB_INSERT,'description'=>string]
 * - ['sucess'=>USER_NOT_LOGGED,'description'=>string] when not logged
 * - ['sucess'=>DB_ERR_INSERT,'description'=>string] when cannot register
 * 
 */
function insert_historico(PDO $conn,array $inputs){
    if(!is_logged()) return ['sucess'=>USER_NOT_LOGGED,'description'=>'not logged'];

    try{
        $field = ['tipo','investimento','valor_a_ser_investido','prazo','investimento_seguinte','percentual_crescimento'];
        //search row with same name
        $query = "
        INSERT INTO historico
        (". join(',',$field) .", user_id".")
        VALUES
        (". named_parameter($field,$inputs) .", :user_id".")
        ";


        //query
        $smtm = $conn->prepare($query);

        $smtm->bindValue(':user_id',$_SESSION['user_id']);
        //bind parameters
        bind_avaible_param($inputs,$field,$smtm);

        if(!($smtm->execute())) throw new Exception("Não consegue inserir esses dados!");
    }catch(Exception $e){
        $conn = NULL;
        return ['sucess'=>DB_ERR_INSERT,'description'=>$e->getMessage()];
    }
    //retrive inserted id
    return ['record_id'=>$conn->lastInsertId(),'sucess'=> DB_INSERT,'description' => 'data saved'];
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