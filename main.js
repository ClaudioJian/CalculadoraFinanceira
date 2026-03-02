//functions
function ClearAllExtendedBox(event){
  event.stopPropagation();

  if(!event.target.closest('[data-type="extendedBox"]') && 
      !event.target.closest('[data-type="extendable"]')){
    extendedBox.forEach(box => {
      if (!box.classList.contains('hidden')) {
        box.classList.add("hidden");
        box.style.width = "";
        box.style.height = "";
        box.style.left = "";
        box.style.right= "";
      }
    });


    extendableElementList.forEach(triggerElement=>{
      if(triggerElement) triggerElement.isExtended = false;
    });

    document.body.removeEventListener("click",ClearAllExtendedBox);
  }
};

function RenderSelectingItem(itemBox, target){
  itemBox.addEventListener("mouseover",(event)=>{
    if(event.target.dataset.order!==undefined)
    event.target.classList.add("selecting-itens");
    }
  );

  //deselecting
  itemBox.addEventListener("mouseout",(event)=>{
  if(event.target.dataset.order!==undefined)
    event.target.classList.remove("selecting-itens");
  });
}

function RenderItens(parentElement,itemBox){
  //parentElement is trigger's parent
  const btnSelector = parentElement.querySelector('[data-action="open-selector"]');

  //find what type is: investimento/imposto/etc
  let targetType;
  targetSelectorList.forEach( s =>{
    if(btnSelector.dataset.targetID === s.dataset.targetID) 
      targetType = s.firstElementChild;
  });

  if(!targetType) console.warn("no valid selector finded! add data-targetID to both selector!");

  //change selecting item visual
  RenderSelectingItem(itemBox);

  //select item-> change to main
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

      });

    itemBox.classList.add('hidden');
    btnSelector.isExtended = false;
  };
  
  itemBox.addEventListener("click",SelectItem);
};

//variable global


//elements
const extendableElementList = document.querySelectorAll('[data-type="extendable"]');
const extendedBox = document.querySelectorAll('[data-type="extendedBox"]');
const selector = document.querySelectorAll('.selector');
const targetSelectorList = document.querySelectorAll('[data-for="filter"]');

//extendableBox
extendedBox.forEach(box=>box.classList.add("hidden"));

extendableElementList.forEach(trigger=>trigger.addEventListener("click",event=>{
  event.stopPropagation();
  const action = trigger.dataset.action;

  trigger.isExtended=!trigger.isExtended;

  let targetElement;
  
  if(action === "open-menu"){
    targetElement = document.body.querySelector('[data-for="menu"]');
    const resizedMenuWidth = JSON.parse(localStorage.getItem('menuSize'));
    
    if(resizedMenuWidth) targetElement.style.width = resizedMenuWidth.width;
  }
  else if(action==="open-selector") {
    const parentElement = trigger.parentNode;
    targetElement = parentElement.querySelector('[data-type="extendedBox"]');
    if(!trigger.initialized) RenderItens(parentElement,targetElement);
  }


  if(trigger.isExtended){
    targetElement.classList.remove("hidden");
    document.body.addEventListener("click",ClearAllExtendedBox);
  }else {
    targetElement.classList.add("hidden");
    targetElement.style.width = "";
  }
  }
))

//resizer
const resizers = document.querySelectorAll('.resizer');

resizers.forEach(resizerEl=>resizerEl.addEventListener("mousedown", event=>{
  event.preventDefault();

  const targetElement = resizerEl.parentNode;
  const classes = resizerEl.classList;
  const storageName = targetElement.dataset.for;

  const sizeTarget = targetElement.getBoundingClientRect();

  const startTop = (parseFloat(targetElement.style.top)||0);
  const startLeft = (parseFloat(targetElement.style.left)||0);
  const startMouseX = event.clientX;
  const startMouseY = event.clientY;


  function moving(e){
    classes.forEach(c=>{
      const distanceY = e.clientY - startMouseY;
      const distanceX =  e.clientX - startMouseX;

      if(c==="js-top") {
        targetElement.style.top = startTop + distanceY + "px";
        targetElement.style.height = sizeTarget.height - distanceY + "px";
      }
      else if(c==="js-bottom") targetElement.style.height = 
        sizeTarget.height + distanceY +"px";
    
      if(c==="js-left") {
        targetElement.style.left = startLeft + distanceX + "px";
        targetElement.style.width = sizeTarget.width - distanceX + "px"
      }
      else if(c==="js-right") targetElement.style.width = 
        sizeTarget.width + distanceX +"px";
    });
  };


  document.body.addEventListener("mousemove",moving);

  document.body.addEventListener("mouseup",function Release(){
    document.body.removeEventListener("mousemove",moving);
    document.body.removeEventListener("mouseup",Release);
    
    const Size = {
      top:targetElement.style.top,
      left:targetElement.style.left,
      width: targetElement.style.width,
      height:targetElement.style.height
    };
    
    localStorage.setItem(`${storageName}Size`,JSON.stringify(Size));
  });
})
);

//resize to default
resizers.forEach(resizerEl=>resizerEl.addEventListener("dblclick", event=>{
  const targetElement = resizerEl.parentNode;
  const classes = resizerEl.classList;

  const distanceTop = -1 * parseFloat(targetElement.style.top)||0;
  const distanceleft = -1 * parseFloat(targetElement.style.left)||0;
  const currSize = targetElement.getBoundingClientRect();
  const storageName = targetElement.dataset.for;
  let setting = JSON.parse(localStorage.getItem(`${storageName}Size`))||{};

  classes.forEach(c=>{
    if(c === "js-top") {
      targetElement.style.top ="";
      targetElement.style.height = currSize.height - distanceTop +"px";

      delete setting.top;
      setting.height = targetElement.style.height;
    }
    else if(c === "js-bottom") {
      targetElement.style.height = "";
      const resetSize = targetElement.getBoundingClientRect();
      targetElement.style.height = resetSize.height + distanceTop +"px";
      setting.height = targetElement.style.height;
    }

    if(c === "js-left") {
      targetElement.style.left ="";
      targetElement.style.width= currSize.width - distanceleft +"px";

      delete setting.left;
      setting.width = targetElement.style.width;
    }
    else if(c === "js-right") {
      targetElement.style.width ="";
      const resetSize = targetElement.getBoundingClientRect();
      targetElement.style.width = resetSize.width + distanceleft +"px";
      setting.width = targetElement.style.width;
    }
  });

  localStorage.setItem(`${storageName}Size`,JSON.stringify(setting));
}
));