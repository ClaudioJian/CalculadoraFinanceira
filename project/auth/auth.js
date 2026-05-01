
const name_field = document.querySelector('input[name="nome"]');
const email_field = document.querySelector('input[name="email"]');

//wait to all element is loaded
window.addEventListener("DOMContentLoaded",()=>{
    const prev_inputs = JSON.parse(localStorage.getItem('Input_storage'));
    if(prev_inputs) {
        name_field.value = prev_inputs.name ?? '';
        email_field.value = prev_inputs.email ?? '';
    }
});



window.addEventListener("beforeunload",(e)=>{
    //set local storage to use in next post
    const inputs=JSON.stringify({
        name : name_field.value,
        email : email_field.value
    });

    localStorage.setItem("Input_storage",inputs);
});

