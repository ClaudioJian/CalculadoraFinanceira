<?php

?>

<script>
    //-------------------------------------------calculator---------------------------------------------------------------------------


    calculators.forEach(calc=>{
    console.log(calc,"calc");

    calc.tabIndex = "0";
    calc.addEventListener("focusin",e=>{
        console.log(e);
    });
    calc.addEventListener("focusout",e=>{
        console.log("blur");
    });
    });


</script>