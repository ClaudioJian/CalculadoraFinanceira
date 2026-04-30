import { CALCULATOR } from "./class/calculator.js";
import * as selector from "./elements/selector.js";
import * as movable from "./elements/movable.js";

//---------------------------------------functions js---------------------------------------------------
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

//-----------------------------------elements changes---------------------------------------------------------------
//hidden all selector's item with depencity
allSelector.forEach(s=>{
  const selectorBox = s.parentNode.querySelector('[data-type="extendedBox"]');
  if(selectorBox.dataset.depencityid) {
    for(let target of selectorBox.children) {
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
    for(let box of extendedBox){
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
      selector.AddSelectEvent(parentElement,itemBox);
      trigger.initialized = true;
    }
    if(itemBox.dataset.depencityid) targetElement = Array.from(selector.GetDepencityTarget(itemBox));
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
  resizerEl.addEventListener("mousedown", event=>movable.FollowMouseChange(event,resizerEl))

  //resize to default
  resizerEl.addEventListener('dblclick',()=>{movable.ResetPos(resizerEl)});
  }
);

draggable.forEach(El=>El.addEventListener("mousedown", event=>movable.FollowMouseChange(event,El)));


//-------------------------------------------calculator---------------------------------------------------------------------------

calculators.forEach(calc=> new CALCULATOR(calc));