const get_btn = document.getElementById("get");
const post_btn = document.getElementById("post");

get_btn.addEventListener("click",async function (e) {
    const result = await get_data();
    console.log(result);
});

document.addEventListener("submit",async (e)=>{
    e.preventDefault();
    
if(e.target.id==="delete_records"){
    const v1 = document.getElementById("record_id1").value;
    const v2 = document.getElementById("record_id2").value;

    const result = await delete_records([v1,v2]);
    if(result['sucess']>0)console.log(result);
}
else
if(e.target.id === "update")
{   
    const v1 = document.getElementById("utipo").value;
    const v2 = document.getElementById("uinvestimento").value;
    const v3 = document.getElementById("uvalor_a_ser_investido").value;
    const v4 = document.getElementById("uprazo").value;
    const v5 = document.getElementById("uinvestimento_seguinte").value;
    const v6 = document.getElementById("upercentual_crescimento").value;

    const v7 = document.getElementById("record_id_update1").value;
    const v8 = document.getElementById("record_id_update2").value;

    const data = {}
    data[v7] =  {
            tipo: v1,
            investimento:v2,
            valor_a_ser_investido:v3,
            prazo:v4,
            investimento_seguinte:v5,
            percentual_crescimento:v6
        }
    if(v8)data[v8] = {tipo: v1,
            investimento:v2,
            valor_a_ser_investido:v3,
            prazo:v4,
            investimento_seguinte:v5,
            percentual_crescimento:v6}



    const result = await update_records(data);
    console.log(result);
}
else{
    const v1 = document.getElementById("tipo").value;
    const v2 = document.getElementById("investimento").value;
    const v3 = document.getElementById("valor_a_ser_investido").value;
    const v4 = document.getElementById("prazo").value;
    const v5 = document.getElementById("investimento_seguinte").value;
    const v6 = document.getElementById("percentual_crescimento").value;


    const result = await insert_data(v1,v2,v3,v4,v5,v6);
    console.log(result);
}
    
});