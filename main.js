//class
class POS {
  constructor(targetElement) {
    this.top = targetElement.style.top;
    this.left = targetElement.style.left;
    this.width = targetElement.style.width;
    this.height = targetElement.style.height;
  }
}

//functions js

function ElementBubbleSort(elementList){
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
  return elementList;
}

//function for html
function ClearAllExtendedBox(event){
  event.stopPropagation();
  const isUIElement = event.target.closest('[data-type="extendedBox"], [data-type="extendable"], .draggable');

  if(!isUIElement){
      extendedBox.forEach(box => {
      if (!box.classList.contains('hidden')) {
        box.classList.add("hidden");
        box.style.width = "";
        box.style.height = "";
        box.style.left = "";
        box.style.right= "";
        }
      });


    extendedBox.forEach(itemBox=>{
      if(itemBox) itemBox.isExtended = false;
    });

    document.body.removeEventListener("click",ClearAllExtendedBox);
  }
};

function RenderSelectingItem(itemBox){
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

function ClearDepedentSelector(firstID){
  const blankElement = document.createElement("div");

  for(const s of allSelector){
    const targetBox = s.parentNode.querySelector('[data-type="extendedBox"]');
    if(targetBox.dataset.depencityid===firstID) {
      
      //replace selector's content with blanck element
      const selected = s.firstElementChild;
      if(selected&&selected.dataset.order) {
        targetBox.appendChild(selected);
        s.appendChild(blankElement);

        let elementList = Array.from(targetBox.children);

        elementList = ElementBubbleSort(elementList);
        elementList.forEach(el=>targetBox.appendChild(el));
      }
      
      targetBox.isExtended = false;
      targetBox.classList.add('hidden');
      //add hidden to all children with depencity
      for(c of targetBox.children){
        if(c.dataset.type!=='all') c.classList.add('hidden')
      };
      
      if(s.dataset.filterid) ClearDepedentSelector(s.dataset.filterid);
    }
  }

}

function AddSelectEvent(parentElement,itemBox){
  //parentElement is trigger's parent
  const btnSelector = parentElement.querySelector('[data-action="open-selector"]');

  //change selecting item visual
  RenderSelectingItem(itemBox);

  //select item-> change to main
  const SelectItem = (event)=>{
    const selected = event.target.closest('[data-order]');
    if(!selected) return;

    //clean all elements
    const oldSelected = btnSelector.firstElementChild;
    const isRealItem = oldSelected && oldSelected.dataset.order!==undefined;
    
    if(isRealItem) selected.replaceWith(oldSelected);
    else btnSelector.innerHTML="";
    btnSelector.appendChild(selected);

    event.target.classList.remove("selecting-itens");
    let elementList = Array.from(itemBox.children);
  
    //sort element by ID, using bubble sort
    elementList = ElementBubbleSort(elementList);
    
    elementList.forEach(el=>itemBox.appendChild(el));
    
    itemBox.isExtended = false;
    itemBox.classList.add('hidden');

    if(btnSelector.dataset.filterid) {
      const currID = btnSelector.dataset.filterid;
      ClearDepedentSelector(currID);
    }
  };
  
  itemBox.addEventListener("click",SelectItem);
};

function GetDepencityTarget(itemBox){
  const depencityID = itemBox.dataset.depencityid;

  for(s of allSelector){
    if(s.dataset.filterid===depencityID) {
      //get what is selected in selector matched
      const selected = s.firstElementChild;

      let targetType;
      let targetElementList=[];

      //find type
      if(selected) targetType = selected.dataset.type;
      else console.warn('selector must contain at least one element, please add blank div!');

      if(selected.dataset.type==="all") targetElementList = itemBox.querySelectorAll('[data-order]');
      //find in itemBox of current selector from data-type of element inside selector matched 
      else{
        targetElementList = itemBox.querySelectorAll(`[data-depencitytype="${targetType}"]`);
        if(targetElementList){
          targetElementList.forEach(el=>{
            if(!el.dataset.depencitytype) console.warn("cannot find depencityID from - ",itemBox);
          });
        }
      }
      return targetElementList;
    }
  }

  console.warn("cannot find selector with data-filterID, you must set filterID as same as depencityID:", depencityID, "from element - ", itemBox.closest('.selector-container'));
}

function returnPos(targetElement) {
  const left = parseFloat(targetElement.style.left) || 0;
  const top = parseFloat(targetElement.style.top) || 0;
  const currSize = targetElement.getBoundingClientRect();
  const btnTarget = targetElement.querySelector('[data-action="open-calculator"]');
  const btnSize = btnTarget.getBoundingClientRect()||0;


  if (left < 0) targetElement.style.left = "0px";
  else if(left+currSize.width>=window.innerWidth) {
    
    targetElement.style.left = `${window.innerWidth - currSize.width}px`;
  }
  if ((top - btnSize.height) < 0) {
    targetElement.style.top = `${btnSize.height}px`;
  }
  else if(top+currSize.height>=window.innerHeight) {
    targetElement.style.top = `${window.innerHeight - currSize.height}px`;
  }
}

function FollowMouseChange(event,El){
  event.preventDefault();
  document.body.removeEventListener("click",ClearAllExtendedBox);

  const targetElement = El.closest('[data-type="extendedBox"]');
  const classes = El.classList;
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

      if(c==="draggable"){
        targetElement.style.top = startTop + distanceY + "px";
        targetElement.style.left = startLeft + distanceX + "px";
      }
      else if(c==="js-top") {
        targetElement.style.top = startTop + distanceY + "px";
        targetElement.style.height = sizeTarget.height - distanceY + "px";
      }
      else if(c==="js-bottom") targetElement.style.height = 
        sizeTarget.height + distanceY +"px";
    
      else if(c==="js-left") {
        targetElement.style.left = startLeft + distanceX + "px";
        targetElement.style.width = sizeTarget.width - distanceX + "px"
      }
      else if(c==="js-right") targetElement.style.width = 
        sizeTarget.width + distanceX +"px";
    });
  };


  document.body.addEventListener("mousemove",moving);

  window.addEventListener("mouseup",function Release(){
    document.body.removeEventListener("mousemove",moving);
    window.removeEventListener("mouseup",Release);
    returnPos(targetElement);
    
    const size = new POS(targetElement);
    
    localStorage.setItem(`${storageName}Size`,JSON.stringify(size));
  });
}

function ResetPos(El,originalPos){
  const targetElement = El.closest('[data-type="extendedBox"]');
  const classes = El.classList;

  const distanceTop = -1 * parseFloat(targetElement.style.top)||0;
  const distanceleft = -1 * parseFloat(targetElement.style.left)||0;
  const currSize = targetElement.getBoundingClientRect();
  const storageName = targetElement.dataset.for;
  let setting = JSON.parse(localStorage.getItem(`${storageName}Size`))||{};

  classes.forEach(c=>{
    if(c === "js-top") {
      targetElement.style.top = originalPos.top;
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
      targetElement.style.left = originalPos.left;
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
//variable global


//-----------------------------------elements-------------------------------------------------------------
const extendableElementList = document.querySelectorAll('[data-type="extendable"]');
const extendedBox = document.querySelectorAll('[data-type="extendedBox"]');
const allSelector = document.querySelectorAll('.selector');
//hidden all selector's item with depencity

//-----------------------------------elements changes---------------------------------------------------------------
allSelector.forEach(s=>{
  const selectorBox = s.parentNode.querySelector('[data-type="extendedBox"]');
  if(selectorBox.dataset.depencityid) {
    for(target of selectorBox.children) {
      //don't add hidden to all
      if(target.dataset.type!=="all") target.classList.add('hidden');
    }
  }
});

//centering calculator
 window.addEventListener('load',()=>{
  const calculator = document.querySelector('[data-for="calculator"]');
  const calcSize = calculator.getBoundingClientRect();

  calculator.style.top = (window.innerHeight/2) - (calcSize.height/2) +'px';
  calculator.style.left = (window.innerWidth/2) - (calcSize.width/2) +'px';
});



//-----------------------------------extendableBox--------------------------------------------------------
extendedBox.forEach(box=>box.classList.add("hidden"));


extendableElementList.forEach(trigger=>trigger.addEventListener("click",event=>{
  event.stopPropagation();
  const action = trigger.dataset.action;

  let targetBox;
  if(trigger&&trigger.dataset.openboxid){
    let count = 0;
    for(box of extendedBox){
      if(trigger.dataset.openboxid===box.dataset.targetboxid) {
        targetBox = box;
        count++;
      }
      if(count>1){console.warn('data-openboxid can only exist one per data-type="extendedBox!"');break;}
    }
  } else targetBox = trigger.parentNode.querySelector('[data-type="extendedBox"]');
  if(targetBox) targetBox.isExtended = !targetBox.isExtended;
  else{console.warn('cannot find element with data-type="extendedBox"')}

  let targetElement =[];
  
  if(action === "open-menu"){
    targetElement = [document.body.querySelector('[data-for="menu"]')];
    const resizedMenuWidth = JSON.parse(localStorage.getItem('menuSize'));
    if(resizedMenuWidth) targetElement[0].style.width = resizedMenuWidth.width;
  }
  else if(action==="open-selector") {
    const parentElement = trigger.parentNode;
    const itemBox = parentElement.querySelector('[data-type="extendedBox"]');
    if(!trigger.initialized) {
      AddSelectEvent(parentElement,itemBox);
      trigger.initialized = true;
    }
    if(itemBox.dataset.depencityid) targetElement = Array.from(GetDepencityTarget(itemBox));
    targetElement.push(itemBox);
  }
  else if(action === "open-calculator"){
    targetElement = [document.body.querySelector('[data-for="calculator"]')];
  }


  if(targetBox.isExtended){
    targetElement.forEach(t => {t.classList.remove("hidden");});
    document.body.addEventListener("click",ClearAllExtendedBox);
  }else {
    targetElement.forEach(t => {
      t.classList.add("hidden");
      if(action==="open-menu") t.style.width='';
    });
  }
  }
));





//-------------------------------------------resizer&&draggable---------------------------------------------------------------------------
const resizers = document.querySelectorAll('.resizer');
const draggable = document.querySelectorAll('.draggable');

resizers.forEach(resizerEl=>resizerEl.addEventListener("mousedown", event=>{
  //start pos
  const targetElement = resizerEl.closest('[data-type="extendedBox"]');
  const startPos = new POS(targetElement);

  FollowMouseChange(event,resizerEl);

  //resize to default
  if(!resizerEl.isInitialized){
      resizerEl.addEventListener('dblclick',()=>{ResetPos(resizerEl,startPos)});
      resizerEl.isInitialized = true;
    }
  }
));

draggable.forEach(El=>El.addEventListener("mousedown", event=>FollowMouseChange(event,El)));

