import { POS } from "../class/POS.js";

//private function
function returnPos(targetElement){
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

//export function for html

const FollowMouseChange = (event,El) => {
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

const ResetPos = (El) => {
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

export {FollowMouseChange, ResetPos};