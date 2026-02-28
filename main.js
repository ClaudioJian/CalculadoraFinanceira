//functions
function ClearAllExtendedBox(event){
  event.stopPropagation();

  if(event.target.dataset.type!=="extendedBox"){
    extendedBox.forEach(box => {
      if (box.classList.hidden === undefined) box.classList.add("hidden");
    });

    extendableElementList.forEach(triggerElement=>{
      if(triggerElement) triggerElement.isExtended = false;
    });

    document.body.removeEventListener("click",ClearAllExtendedBox);
  }
};

function RenderItens(parentElement,itemBox){
  const btnSelector = parentElement.querySelector('[data-action="open-selector"]');
  itemBox.addEventListener("mouseover",(event)=>{
      if(event.target.dataset.order!==undefined)
      event.target.classList.add("selecting-itens");
    }
  );

  itemBox.addEventListener("mouseout",(event)=>{
      if(event.target.dataset.order!==undefined)
        event.target.classList.remove("selecting-itens");
    }
  );

  const SelectItem = (event)=>{
    const selected = event.target.closest('[data-order]');

    //clean all elements
    const oldSelected = btnSelector.firstElementChild;
    if(oldSelected) selected.replaceWith(oldSelected);
    btnSelector.appendChild(selected);

    event.target.classList.remove("selecting-itens");
    const elementList = Array.from(itemBox.children);
    
    //sort element by ID, using bubble sort
    for(let i=0;i<elementList.length;i++){
      for(let j=1;j<elementList.length-i;j++){
        let before = elementList[j-1];
        const beforeOrder = Number(before.dataset.order);
        const currOrder = Number(elementList[j].dataset.order);

        if(beforeOrder > currOrder) {
          elementList[j-1] = elementList[j];
          elementList[j] = before;
        }else if(beforeOrder === currOrder) console.warn("data-order must be different!");
      }
    }
    
    elementList.forEach(el=>{
      itemBox.appendChild(el);
      }
    );
  };
  
  itemBox.addEventListener("click",SelectItem);
};


//variable global


//elements
const extendableElementList = document.querySelectorAll('[data-type="extendable"]');
const extendedBox = document.querySelectorAll('[data-type="extendedBox"]');
const selector = document.querySelectorAll('.selector');

extendedBox.forEach(box=>box.classList.add("hidden"));

extendableElementList.forEach(trigger=>trigger.addEventListener("click",event=>{
  event.stopPropagation();
  const action = trigger.dataset.action;

  trigger.isExtended=!trigger.isExtended;

  let targetElement;
  
  if(action === "open-menu"){
    targetElement = document.body.querySelector('[data-for="menu"]');
  }
  else if(action==="open-selector") {
    const parentElement = trigger.parentNode;
    targetElement = parentElement.querySelector('[data-type="extendedBox"]');
    if(!trigger.initialized) RenderItens(parentElement,targetElement);
  }


  if(trigger.isExtended){
    targetElement.classList.remove("hidden");
    document.body.addEventListener("click",ClearAllExtendedBox);
  }else targetElement.classList.add("hidden");
  }
))