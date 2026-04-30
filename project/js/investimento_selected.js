/*
--------------------------Instruction--------------------------
How to use:
1. import it in your file which you want use, in top of file, enter:
import {Investment} from path/to/this_file/investment_selected.js;

2. to use these function, enter: Investment.function_name(), for example: Investment.getSelectedData("Tipo_de_Investimento");


Features:
1. getSelectedData(id) - 
    return the type of selected item in selector with id, if no item selected by user, return false. for example, 
    if user select "Renda Fixa" in selector with id "Tipo_de_Investimento", it will return "Renda Fixa", if user not select any item, it will return false.
2. isAllRequiredSelected(RequiredID_Arr) - 
    RequiredID_Arr: pass this argument as an array contain id for selector, for example ["Tipo_de_Investimento","Prazo"]. If not passed or is string "all"(case insensitive),
    all selector will become required.
    This function will check if all required selector has been selected. if true, it will return object with attributte named as id of selector and value will be type of selected item.
    if returned false, it mean any of required failed meet requirement. 
    for those non required selector, it will automatically fill with false for not selected else will be filled by type of selected item. In this case, you must define default value by using an array or retrieve from either database or json

    Example of what will return:
    from selector - 
    <section id="Tipo_de_Investimento" class="...">
        <button data-action="open-selector" class="...">
            <ul class="..." data-type="Renda Fixa" data-order="...">Renda</ul>
        </button>
        ...
    </section>

    result returned -
    {"tipo_de_investimento":"Renda Fixa", ...(others selectors if exist)}
*/




export const Investment = {
    getSelectedData : function(id) {
        try{
            const container = document.getElementById(id);
            if(!container) throw new Error("cannot find container with id - ", id);

            const selected = container.querySelector('[data-action="open-selector"]');
            if(!selected) {throw new Error("invalid structure - ", id)};

            const selectedItem = selected.firstElementChild;
            //false if no item selected
            return selectedItem.dataset.type ?? false;
        }catch(e){
            console.error(e.message);
            return false;
        }
    },

    isAllRequiredSelected : function(RequiredID_Arr = "all"){
        let data = {};
        //get all selector id in page
        const IDList = Array.from(allSelector).map(s=>s.id);

        if(!Array.isArray(RequiredID_Arr) && RequiredID_Arr == "all") RequiredID_Arr = IDList;
        
        // check if required id passed is selected, if true, return array with all item's type else return false
        for(let s of allSelector){
            const type = this.getSelectedData(s.id);

            if(RequiredID_Arr.includes(s.id) && !type) return false;
            // at this point, if is not required don't care, since for not required will fill with false or what has required, if is required, it cannot be false since code above alredy check that
            data[s.id] = type;
        }

        return data;
    }
}