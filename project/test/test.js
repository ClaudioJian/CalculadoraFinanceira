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
{   const v1 = document.getElementById("tipo").value;
    const v2 = document.getElementById("investimento").value;
    const v3 = document.getElementById("valor_a_ser_investido").value;
    const v4 = document.getElementById("prazo").value;
    const v5 = document.getElementById("investimento_seguinte").value;
    const v6 = document.getElementById("percentual_crescimento").value;

    const result = await insert_data(v1,v2,v3,v4,v5,v6);
    if(result['sucess']>0)console.log(result);
}
    
});