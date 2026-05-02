//para que o página pode usar esses função, coloque isso:
//<script src="js/grab_data_from_database/function_for_grab_and_insert.js"></script>


/**
 * Fetches user investment history from the server.
 * * @async
 * @returns {Promise<Array|null>} 
 * - Success: Returns an Array of objects [{tipo, prazo, ...}]
 * - Error: Returns null and logs the error to the console.
 * * @example
 * // Usage in another file:
 * const history = await get_data();
 * * if (history) {
 * // Accessing the first record:
 * console.log(history[0].tipo); 
 * console.log(history[0].valor_a_ser_investido);
 * }
 */
async function get_data(){
    const response = await fetch("./../historico.php",{
        method:"GET",
    });

    try{
        //tem erro quando pegar
        if(!response.ok) {
            throw new Error(`the request failed with code ${response.status}`);
        }
        const result = await response.json();
        return result;
    }catch(error){
        console.error(error.message);
        return null;
    }
}


// para usar-lo: await insert_data(tipo,investimento,valor_a_ser_investido,prazo,investimento_seguinte,percentual_crescimento);
// check se inserido: const result =  async ()=>await insert_data(); if(result['sucess']<=0) <- error
async function insert_data(tipo,investimento,val_inv,prazo,inv_next,perc_cresc){
    const response = await fetch("./../historico.php",{
        method:"POST",
        body:JSON.stringify({
            tipo : tipo,
            investimento:investimento,
            valor_a_ser_investido:val_inv,
            prazo:prazo,
            investimento_seguinte:inv_next,
            percentual_crescimento:perc_cresc
        })
    });

    try{
        //tem erro quando pegar
        if(!response.ok) {
            console.log(response.ok);
            throw new Error(`the request failed with code ${response.status}`);
        }
        const result = await response.json();
        return result;
    }catch(error){
        console.error(error.message);
        return null;
    }
}