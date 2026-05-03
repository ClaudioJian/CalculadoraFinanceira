/*
para que o página pode usar esses função, coloque isso:
<script src="js/grab_data_from_database/function_for_grab_and_insert.js"></script>

resultado retornado sempre tem: result['sucess'] e result['description']
se result['sucess'] <= 0, o não foi sucesso

function can uses: get_data();
insert_data(tipo,investimento,val_inv,prazo,inv_next,perc_cresc);
delete_records(array_ids); // array_id = [record_id1,record_id2,...]. vai tem atributo adicional ['affected_rows'] que indica quantos deletou

*/






















const DB_INSERT = 52;
const DB_DELETE = 54;

/**
 * Fetches user investment history from the server.
 * 
 * Need parent function be async!!!
 * * @async
 * @returns {Promise<object|null>} 
 * - Success: Returns an objects [n:{tipo, prazo, ...},sucess:int,description:string]
 * - Fail : Returns an object [sucess: negative int,description:string]
 * - Error: Returns null and logs the error to the console
 * @example
 * // Usage in another file:
 * const any_function = async ()=>{const history = await get_data();return history;}
 * const history = any_function();
 * if (history) {
 * // Check if action sucess
 * if(history['sucess'] <= 0) console.error(history['description']);
 * // Accessing the first record(don't acess them unless you checked if action sucess):
 * console.log(history[0].tipo); or console.log(history[0][tipo])
 * console.log(history[0].valor_a_ser_investido); or console.log(history[0][valor_a_ser_investido]);
 * }
 * //OR
 * whateverElement.addEventListener("anyaction",async (event)=>{
 *  const history = await get_data();
 *  //others codes go here
 * })
 */
async function get_data(){
    const response = await fetch("./../historico.php",{
        method:"GET",
    });

    const result = await check_response(response);
    if(result!==null) return result;
    else return null;
}




/** 
 * 
 * Need parent function be async!!!
 * * @async
 * @example
 * // $data is (tipo,investimento,val_inv,prazo,inv_next,perc_cresc)
 * async any_function($data){const result = await insert_data($data); return result;}
 * const response = any_function(data);
 * if(response['sucess']<=0) ... <- action failed
 * //OR
 * whatever.addEventListener("anyEvent",async(event)=>{
 *  const response = await insert_data($data);
 *  //... others codes go here
 * });
 * @returns {Promise<object|null>} 
 * - Sucess: Returns [sucess:int,description:string]
 * - Fail : Returns an object [sucess: negative int,description:string] and logs the error to the console.
 * - Error: Returns null and logs the error to the console.
*/
async function insert_data(tipo,investimento,val_inv,prazo,inv_next,perc_cresc){
    const response = await fetch("./../historico.php",{
        method:"POST",
        body:JSON.stringify(
            {
            data:{ 
                    tipo : tipo,
                    investimento:investimento,
                    valor_a_ser_investido:val_inv,
                    prazo:prazo,
                    investimento_seguinte:inv_next,
                    percentual_crescimento:perc_cresc
                },
            code:DB_INSERT,
            description:"INSERT"
        })
    });

    const result = await check_response(response);
    if(result!==null) return result;
    else return null;
}

/**
 * delete rows in database historico indicated by array passed to param
 * 
 * Need parent function be async!!!
 * 
 * @param array arr_id [record_id1,record_id2,...].
 * @async
 * @example

 * @returns {Promise<object|null>} 
 * - Sucess: Returns [sucess:int,description:string,'affected_rows'=>int unsigned]
 * - Fail : Returns an object [sucess: negative int,description:string] and logs the error to the console.
 * - Error: Returns null and logs the error to the console.
*/
async function delete_records(arr_id){
    const response = await fetch("./../historico.php",{
        method:"POST",
        body:JSON.stringify(
            {
            data:arr_id,
            code:DB_DELETE,
            description:"DELETE ROWS"
        })
    });


    const result = await check_response(response);
    if(result!==null) return result;
    else return null;
}

/**
 * Handles responses
 * @param {Promise<object>} response 
 * @returns {JSON<object>|null}
 * - Sucess: return json object
 * - Fail: null and logs the error to the console.
 */
async function check_response(response){
    try{
        //tem erro quando pegar
        if(!response.ok) {
            console.log(response.ok);
            throw new Error(`the request failed with code ${response.status}`);
        }

        const result = await response.json();
        if(result['sucess']<=0) console.warn("error exited with code - [" , result['sucess'] , "] : ",result['description']);
        return result;
    }catch(error){
        console.error(error.message);
        return null;
    }
}