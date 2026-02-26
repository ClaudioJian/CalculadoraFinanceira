//functions
function ClearAllExtendedBox(event){
  event.stopPropagation();

  if(event.target.dataset.type!=="extendable"){
    let i=0;

    extendedBoxArr.forEach(box => {
      const triggerElement = box.parentNode; // Find who the parent actually is
      if (triggerElement) {
        triggerElement.isExtended = false;
        triggerElement.removeChild(box);
      }
    });

    extendedBoxArr = [];
    document.body.removeEventListener("click",this);
  }
};

const AppendMenu = (targetElement,trigger)=>{
  targetElement.classList.add("css-menu");
  trigger.isExtended = true;

  trigger.appendChild(targetElement);
}


//variable
let extendedBoxArr = [];

//elements
const extendableElementList = document.querySelectorAll('[data-type="extendable"]');


extendableElementList.forEach(trigger=>trigger.addEventListener("click",event=>{
  event.stopPropagation();
  const action = trigger.dataset.action;
  let newBox;

  if(!trigger.isExtended) newBox = document.createElement("div");
  else return;

  extendedBoxArr.push(newBox);

  if(action === "open-menu") AppendMenu(newBox ,trigger);
  //else if(action==="")
  

  document.body.addEventListener("click",ClearAllExtendedBox);

})
)