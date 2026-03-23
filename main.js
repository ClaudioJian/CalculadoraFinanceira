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

class CALCULATOR{
  constructor(calc){
    this.calc = calc;

    this.displayStr = [];

    //element
    this.btn = this.calc.querySelectorAll('[data-value]');
    this.resultEl = this.calc.querySelector('[data-for="result"]');
    this.prevOpEl = this.calc.querySelector('[data-for="previousoperation"]');

    //attributte to track which button is clicked
    this.targetbtn = {
      key : '',
      type : ''
    };

    // Helper array to check if a character is an operator
    this.operationValue = ['+', '-', '*', '/', '(', ')'];

    this.priority = {
      '=' : -99,
      '(' : -2, ')': -1,
      '+' : 1, '-' : 1,
      '*' : 2, '/' : 2,
    }

    this.currNumber = '';
    this.has_dot = false;
    this.prevKey = {value:'',type:''};

    //array to waiting for priority end calculations
    this.awaitOperation = [];
    this.awaitValue = [];
    
    this.HandleKeyDown = this.HandleKeyDown.bind(this);

    this.init();
  }

  HandleKeyDown(e){
    e.preventDefault();
    
    let btnEl; //reset

    switch(e.key){
      case 'Enter': 
        btnEl = this.calc.querySelector('[data-value="="]') ?? null; 
        break;
      case 'Backspace': 
        btnEl = this.calc.querySelector('[data-value="backspace"]') ?? null; 
        break;
      default:
        this.btn.forEach(button=>{
          if(e.key===button.dataset.value) btnEl = button;
        });
      break;
    }
    if(btnEl) btnEl.click();
  }

  Parenteses_Open(){
    if(
      (this.prevKey.value === ')')
      ||(this.prevKey.type !== 'operator' && this.currNumber !== '')
    ) {
      this.awaitOperation.push('*');
      this.displayStr.push('');
    }// 5*(
  }

  PrevMultipleOperator(){
    //shortcut
    const key = this.targetbtn.key;
    
    //update stack
    if(this.awaitOperation.length>0) {
      this.awaitOperation.pop();
      this.displayStr.pop();
    }

    this.prevKey.value = key;
    this.prevKey.type = this.targetbtn.type;

    if(key==='=') {
      this.resultEl.innerText = '';
      this.displayStr.forEach(s=>this.resultEl.innerText+=s);
    }
  }

  ResetData(){
    this.awaitValue = [];
    this.awaitOperation = [];
    this.displayStr = [];
    if(this.targetbtn.key!=='=') this.currNumber = '';
    this.targetbtn = {key:'',type:''};

    
  }

  UpdateUI(){
    //change result to prevOpEl and show only result
    if(this.targetbtn.key === '=') {
      const result = this.currNumber||this.awaitValue.pop();
      //pull real display str to prevOp
      this.prevOpEl.innerText = this.resultEl.innerText + '=';
      this.resultEl.innerText = result;

      this.ResetData();

      this.has_dot = toString(this.currNumber).includes('.');
    }else{
      this.resultEl.innerText = '';

      this.displayStr.forEach(s=>{
        this.resultEl.innerText +=s;
      });
    }
  }

  Solve(op){
    let prevVal = this.awaitValue.pop();
    let result;
    let deleted;
    
    prevVal = prevVal === '.' ? 0 : parseFloat(prevVal);

    //if is empty string, mean before is operator, then it alredy is in stack, need pull out. example: (2)+...
    if(this.currNumber === ''){
      this.currNumber = prevVal;
      prevVal = parseFloat(this.awaitValue.pop());
      if(this.targetbtn.key !== '=') deleted = this.displayStr.pop();
      if(deleted === ')'|| deleted ==='(') this.displayStr.pop();
    }
    else this.currNumber = this.currNumber === '.' 
      ? 0 
      : parseFloat(this.currNumber); 
    

    if(isNaN(prevVal)) return this.currNumber;

    
    switch(op){
      case '*' : result = prevVal * this.currNumber; break;
      case '/' : result = prevVal / this.currNumber; break;
      case '+': result = prevVal + this.currNumber; break;
      case '-': result = prevVal - this.currNumber; break;
    }

    
    //pop 2 time displayStr
    for(let i = 0;i<2;i++) {
      const deletedS = this.displayStr.pop();
      if(deletedS==='('||deletedS===')') this.displayStr.pop();
    };
    return result;
  }

  SolveStack(){
    //shortcut
    const key = this.targetbtn.key;
    const currPriority = this.priority[key];
    let op;

    while(this.awaitOperation.length > 0 ){
      op = this.awaitOperation.pop();

      if(op === '(' && key=== '=') continue;

      if(currPriority > this.priority[op]){
        if(key !==')')this.awaitOperation.push(op);
        break;
      }

      //remove ) from stack and skip this
      // update displayStr, deleting number op number to replace with result
      this.currNumber = this.Solve(op);
    }
  }

  AppendDisplayStr(){
    const key = this.targetbtn.key;

    (!this.currNumber)||this.displayStr.push(this.currNumber);

    (!this.currNumber)||this.awaitValue.push(this.currNumber);
    
    //prevent ) or = push into awaitoperation
    if(key !==')') this.awaitOperation.push(key);

    //flip has dot to active for next number, only do after check key==='=' to prevent next number forgot .
    if(this.has_dot) this.has_dot = false;


    //only add operator if has number on it
    if(key === '(' ||(this.resultEl.innerText.length>=1)) {
      this.resultEl.innerText += key;
      this.displayStr.push(key);
    }
  }

  OperatorCases(){
    //return: 0 continue 1 leave
    //shortcut
    const key = this.targetbtn.key;
    const swappablePrevOp = ['+','-','*','/'];
    const swappableOp =  ['+','-','*','/',')','='];

    // put in stack and end
    if(key==='(') {
      this.Parenteses_Open(); 
      return 0;
    }
    //if previous key is also operator and can be swap like +- to -
    else if(swappableOp.includes(key)&&swappablePrevOp.includes(this.prevKey.value)) this.PrevMultipleOperator();

    this.SolveStack(); 
      
    return 0;
  }

  OperatorHandler(){
    const key = this.targetbtn.key;
    this.targetbtn.type = 'operator';

    //avoid operators after (
    if(this.prevKey.value === '(') return 1;

    if(this.OperatorCases()) return 0;

    if(key!=='=') this.AppendDisplayStr();

    this.UpdateUI();
    if(key!=='=') this.currNumber = '';
  }

  Backspace(){
    const deletedChar = this.resultEl.innerText.slice(-1);
    if(!deletedChar) return;

    this.displayStr.pop();

    if(this.operationValue.includes(deletedChar)){
      //buffer to store all operator until hit '(' and restore it back to awaitOperation
      const bufferOp = [];
      if(deletedChar===')'){
        for(let i = this.displayStr.length-1; i>=0;i--){
          const prevStr = this.displayStr[i];
          // avoid (5+(5)) immedialy push back ( to await op even is alredy solved
          if(prevStr===')'){
            //since ) mean alredy solved, skip 2(1 will be remove automatically) index to skip (. also we need avoid 5)+2) situation
            i -= this.displayStr[i-3] === '(' ? 2:1;
          }
          else if(prevStr!=='(' && this.operationValue.includes(prevStr)){
            const delOp = this.awaitOperation.pop();
            bufferOp.push(delOp);
          }else if(prevStr==='('){
            this.awaitOperation.push('(');
            for(const op of bufferOp.reverse()){this.awaitOperation.push(op);}
          }
        }
      }else{
        if(this.awaitOperation.length>0) this.awaitOperation.pop();

        this.currNumber = this.awaitValue.pop()||'';
        this.has_dot = String(this.currNumber).includes('.');
        // value is in currValue now and displaystr isn't in display yet, currNumber will only be pushed in displayStr if user press operator
        this.displayStr.pop();
      }
    }else{
      //update current number
      this.currNumber = String(this.currNumber).slice(0,-1);
      
      if(deletedChar==='.') this.has_dot = false;

      if(this.currNumber!=='') this.displayStr.push(this.currNumber);
    } 

    const prevStr = this.displayStr[this.displayStr.length-1];
    this.prevKey.value = prevStr.slice(-1);
    this.prevKey.type = this.operationValue.includes(prevStr) ? 'operator':'';

    this.resultEl.innerText = this.resultEl.innerText.slice(0,-1);
    return 1;
  }

  AddCharInResult(){
    //shortcut
    const key = this.targetbtn.key;

    if(this.prevKey.value===')') return 1;

    if(key.length===1) {
      //)5 is invalid, at least need operator

      if(this.awaitOperation[this.awaitOperation.length-1]===')') return;
      //prevent 1.2.22 float number
      if(key ==='.') {
        if(this.has_dot) return;
        else this.has_dot = true;
      }

      //add only key with 1 char
      this.resultEl.innerText += key;
      this.currNumber += key;
    }
    this.targetbtn.type = '';
    return 0;
  }

  CalculatorLogic(e,b){
    let returnCode = 0;
    this.targetbtn.key = b.dataset.value ?? null;
    this.targetbtn.type = b.dataset.type ?? null;


    if(!this.targetbtn.key) {console.error("you must define data-value! ",targetbtn.key," in ",b);return;}

    if(this.targetbtn.type && this.targetbtn.type === 'operator') returnCode = this.OperatorHandler();
    else if(this.targetbtn.key === 'backspace') returnCode = this.Backspace();
    else if(this.targetbtn.key.length === 1) returnCode = this.AddCharInResult();

    if(returnCode) return;

    this.prevKey.value = this.targetbtn.key;
    this.prevKey.type = this.targetbtn.type;
  }

  init(){
    //make it interactable
    this.calc.tabIndex='1';

    this.calc.addEventListener("focusin",e=>{document.addEventListener("keydown",this.HandleKeyDown);});
    this.calc.addEventListener("focusout",e=>{document.removeEventListener("keydown",this.HandleKeyDown);});

    this.btn.forEach(b=>b.addEventListener("click",e=>this.CalculatorLogic(e,b)));
  }
}


//---------------------------------------functions js---------------------------------------------------

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



calculators.forEach(calc=> new CALCULATOR(calc));


