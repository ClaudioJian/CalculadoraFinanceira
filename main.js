//class
class POS {
  constructor(targetElement,resizedSize={}) {
    this.top = targetElement.style.top;
    this.left = targetElement.style.left;
    this.width = targetElement.style.width;
    this.height = targetElement.style.height;
    this.resizedSize = resizedSize;
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
        if(box.dataset.for==='menu') box.style.width ='';
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
  if(!targetElement) return;
  const left = parseFloat(targetElement.style.left) || 0;
  const top = parseFloat(targetElement.style.top) || 0;
  const currSize = targetElement.getBoundingClientRect();
  const elementDataFor = targetElement.closest('[data-for]');
  let btnSize = 0;
  let btnTarget;
  if(elementDataFor&&elementDataFor==='calculator'){
    btnTarget = targetElement.querySelector('[data-action="open-calculator"]');
  }
  
  btnSize = btnTarget?btnTarget.getBoundingClientRect():{};


  if (left < 0) targetElement.style.left = "0px";
  else if(left+currSize.width>=window.innerWidth) {
    targetElement.style.left = `${window.innerWidth - currSize.width}px`;
  }
  if ((top - btnSize.height) < 0) {
    targetElement.style.top = `${btnSize?btnSize.height:0}px`;
  }
  else if(top+currSize.height>=window.innerHeight) {
    targetElement.style.top = `${window.innerHeight - currSize.height}px`;
  }
}

function FollowMouseChange(event,El){
  event.preventDefault();

  const targetElement = El.closest('[data-type="extendedBox"]');
  targetElement.focus();

  const direction = El.dataset.direction;
  const storageName = targetElement.dataset.for;

  const sizeTarget = targetElement.getBoundingClientRect();
  const resizedSize = {
    distTop:0,
    distLeft:0,
    distRight:0,
    distBottom:0
  };

  const startTop = (parseFloat(targetElement.style.top)||0);
  const startLeft = (parseFloat(targetElement.style.left)||0);
  const startMouseX = event.clientX;
  const startMouseY = event.clientY;

  function moving(e){
    const distanceY = e.clientY - startMouseY;
    const distanceX =  e.clientX - startMouseX;

    

    if(direction==="draggable"){
      targetElement.style.top = startTop + distanceY + "px";
      targetElement.style.left = startLeft + distanceX + "px";
    }
    else{ 
      if(direction.includes("n")) {
        targetElement.style.top = startTop + distanceY + "px";
        targetElement.style.height = sizeTarget.height - distanceY + "px";
        resizedSize.distTop = -distanceY;
      }
      else if(direction.includes("s")){
        targetElement.style.height = sizeTarget.height + distanceY +"px";
        resizedSize.distBottom = distanceY;
      }
    
      if(direction.includes("w")) {
        targetElement.style.left = startLeft + distanceX + "px";
        targetElement.style.width = sizeTarget.width - distanceX + "px";
        resizedSize.distLeft = -distanceX;
      }
      else if(direction.includes("e")){
        targetElement.style.width = sizeTarget.width + distanceX +"px";
        resizedSize.distRight = distanceX;
      }
    }
  };

  document.body.addEventListener("mousemove",moving);

  window.addEventListener("mouseup",function Release(){
    targetElement.blur();
    document.body.removeEventListener("mousemove",moving);
    window.removeEventListener("mouseup",Release);
    returnPos(targetElement);

    const oldSizeStr = localStorage.getItem(`${storageName}Size`);
    const oldSize = oldSizeStr ? JSON.parse(oldSizeStr):null;
    if(oldSize&&oldSize.resizedSize){
      resizedSize.distTop += oldSize.resizedSize.distTop;
      resizedSize.distBottom += oldSize.resizedSize.distBottom;
      resizedSize.distLeft += oldSize.resizedSize.distLeft;
      resizedSize.distRight += oldSize.resizedSize.distRight;
    }
    const size = new POS(targetElement,resizedSize);
    
    localStorage.setItem(`${storageName}Size`,JSON.stringify(size));
  });
}

function ResetPos(El){
  const targetElement = El.closest('[data-type="extendedBox"]');
  const direction = El.dataset.direction;

  const storageName = targetElement.dataset.for;
  let setting = JSON.parse(localStorage.getItem(`${storageName}Size`));
  if(!setting) return;

  const resized = setting.resizedSize||{};

  const moveTop = resized.distTop||0;
  const moveBottom = resized.distBottom||0;
  const moveRight = resized.distRight||0;
  const moveLeft = resized.distLeft||0;

  if(direction.includes("n")) {
    targetElement.style.top = (parseFloat(setting.top) + moveTop) + 'px';
    targetElement.style.height = (parseFloat(setting.height) - moveTop) + "px";

    setting.height = targetElement.style.height;
    setting.top = targetElement.style.top;
    resized.distTop = 0;
  }
  else if(direction.includes("s")) {
    targetElement.style.height = (parseFloat(setting.height) - moveBottom) +"px";

    setting.height = targetElement.style.height;
    resized.distBottom = 0;
  }

  if(direction.includes("w")) {
    targetElement.style.left = (parseFloat(setting.left) + moveLeft) + 'px';
    targetElement.style.width = (parseFloat(setting.width) - moveLeft) + "px";

    setting.width = targetElement.style.width;
    setting.left = targetElement.style.left;
    resized.distLeft = 0;
  }
  else if(direction.includes("e")) {
    targetElement.style.width = (parseFloat(setting.width) - moveRight) +"px";

    setting.width = targetElement.style.width;
    resized.distRight = 0;
    
  }

  setting.resizedSize = resized;
  localStorage.setItem(`${storageName}Size`,JSON.stringify(setting));
}
//variable global


//-----------------------------------elements-------------------------------------------------------------
const extendableElementList = document.querySelectorAll('[data-type="extendable"]');
const extendedBox = document.querySelectorAll('[data-type="extendedBox"]');
const allSelector = document.querySelectorAll('.selector');

const resizers = document.querySelectorAll('.resizer');
const draggable = document.querySelectorAll('.draggable');
const calculators = document.querySelectorAll('[data-for="calculator"]');

//-----------------------------------elements changes---------------------------------------------------------------
//hidden all selector's item with depencity
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
  resizers.forEach(s=>{
    const elementFind = s.closest('[data-for]');
    const target = elementFind.dataset.for;
    if(elementFind&&target!=='menu') {
      const targetJSON = JSON.parse(localStorage.getItem(`${target}Size`));
      if(targetJSON){
        elementFind.style.top = targetJSON.top;
        elementFind.style.left = targetJSON.left;
        elementFind.style.width = targetJSON.width;
        elementFind.style.height = targetJSON.height;
      }
    }else return;
  });

  const isMovedCalc = localStorage.getItem('calculatorSize');
  if(!isMovedCalc){
    const calculator = document.querySelector('[data-for="calculator"]');
    const calcSize = calculator.getBoundingClientRect();

    calculator.style.top = (window.innerHeight/2) - (calcSize.height/2) +'px';
    calculator.style.left = (window.innerWidth/2) - (calcSize.width/2) +'px';
  }
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

  let targetElement = [];
  
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
  else if(action === "open-debugger"){
    targetElement = [document.body.querySelector('[data-for="debug"]')];
  }

  //error menssage
  if(targetElement.length===0||!targetBox)
    console.error("cannot find targetElement!",{
    action: action??"data-action not set in button!",
    btn: trigger,
    targetBox: targetBox??{
      openBoxid: trigger.dataset.openboxid??"data-openboxid not set in button!",
      }
    }
  );
  else if(targetBox.isExtended){
    //show element
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


resizers.forEach(resizerEl=>{
  resizerEl.addEventListener("mousedown", event=>FollowMouseChange(event,resizerEl))

  //resize to default
  resizerEl.addEventListener('dblclick',()=>{ResetPos(resizerEl)});
  }
);

draggable.forEach(El=>El.addEventListener("mousedown", event=>FollowMouseChange(event,El)));


//-------------------------------------------calculator---------------------------------------------------------------------------


calculators.forEach(calc=>{
  //make it interagible
  calc.tabIndex='1';

  //elements
  const resultEl = calc.querySelector('[data-for="result"]');
  const prevOp = calc.querySelector('[data-for="previousoperation"]');
  const btn = calc.querySelectorAll('[data-value]');
  //all operation
  const operation = calc.querySelectorAll('[data-type="operator"]');
  const operationValue = [];
  operation.forEach(op=>operationValue.push(op.dataset.value)); 

  let prevKey;

  function keyboardClickBtn(e){
    if(e.key==='Enter') {
      e.preventDefault();
      const equalBtn = calc.querySelector('[data-value="="]');
      if (equalBtn) equalBtn.click();
    }else{
    btn.forEach(button=>{
      if(e.key===button.dataset.value) button.click();
      });
    }
  }

  calc.addEventListener("focusin",e=>{
    //change text inside it
    document.addEventListener("keydown",keyboardClickBtn);
  });

  calc.addEventListener("focusout",e=>{
    document.removeEventListener("keydown",keyboardClickBtn);
  });

  btn.forEach(b=>{
    b.addEventListener("click",event_c=>{
      const key = b.dataset.value;
      if(!key) console.error("you must define data-value! ",key," in ",b);
      else{
        if((operationValue.includes(key)&&operationValue.includes(prevKey))||key==='Backspace'){
          resultEl.innerText = resultEl.innerText.slice(0,-1);
        }
        resultEl.innerText += key;
      }
      if(key==='='&& resultEl.innerText.length>1) resultEl.innerText = eval(resultEl.innerText.replace('=',''));
      prevKey = key!=='='?key:prevKey;
    });
  })
});


