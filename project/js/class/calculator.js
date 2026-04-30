export class CALCULATOR{
  constructor(calc){
    this.calc = calc;

    this.displayStr = [];

    //element
    this.btn = this.calc.querySelectorAll('[data-value]');
    this.resultEl = this.calc.querySelector('[data-for="result"]');
    this.prevOpEl = this.calc.querySelector('[data-for="previousOperation"]');

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
      (!this.currNumber) || this.awaitValue.push(this.currNumber);

      (!this.currNumber) || this.displayStr.push(this.currNumber);
      this.displayStr.push('');

      this.currNumber = '';
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

    result = String(result);

    let openParenteses = false;
    let endFlag = false;
    //
    while(this.displayStr.length>0) {
      const deletedS = this.displayStr.pop();
      if(!deletedS) break;
      //delete '(' only when it is paired with ')'
      if(deletedS === ')') {
        openParenteses = true; continue;
      }
      if(deletedS === String(prevVal) && openParenteses) {
        this.displayStr.pop();
        openParenteses = false;
      } 
      if(endFlag) break;
      if(this.operationValue.includes(deletedS) && deletedS!=='(' && deletedS!==')') endFlag = true;
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
    if(this.prevKey.value === '(' && key !== '(') return 1;

    if(this.OperatorCases()) return 0;

    if(key === ')' && this.currNumber==='') this.currNumber = 0; //prevent user enter ()
    if(key!=='=') this.AppendDisplayStr();
    

    this.UpdateUI();
    if(key!=='=') this.currNumber = '';
  }

  ReconstructAwaitOp(){
    this.awaitOperation = [];

    for(let i = 0; i <= this.displayStr.length-1 ; i++){
      const currItem = String(this.displayStr[i]);

      if(currItem === '') this.awaitOperation.push('*');
      else if(this.operationValue.includes(currItem)){
        if(currItem === ')') this.awaitOperation.pop();
        else this.awaitOperation.push(currItem); 
      }
    }
  }

  DeleteParenteses_open(deleted){
    if(deleted === ''){
      this.awaitOperation.pop(); //pop 2(2 case
      this.displayStr.pop();
      this.currNumber = this.awaitValue.pop()||'';
    }else if(deleted) this.displayStr.push(deleted);
  }

  Backspace(){
    const deletedChar = this.resultEl.innerText.slice(-1);
    if(!deletedChar) return;

    if(this.operationValue.includes(deletedChar)){
      const deletedOp = this.displayStr.pop(); //pop operator

      if(deletedChar===')') this.ReconstructAwaitOp();
      else{
        this.has_dot = String(this.currNumber).includes('.'); 

        if(this.awaitOperation.length>0) this.awaitOperation.pop();
  
        //avoid previous char is ')' and pull number from stack
        if(this.displayStr[this.displayStr.length-1] !== ')') {
          const deleted = this.displayStr.pop();
          
          if(deletedOp==='(') this.DeleteParenteses_open(deleted);
          else this.currNumber = this.awaitValue.pop()||''; //standart deletion
        }
      }// when hit ')', currNumber is pushed into awaitvalue
    }else{
      //update current number
      this.currNumber = String(this.currNumber).slice(0,-1);
      
      if(deletedChar==='.') this.has_dot = false;

      if(this.currNumber!=='') this.displayStr.push(this.currNumber);
    } 

    this.resultEl.innerText = this.resultEl.innerText.slice(0,-1);

    const prevChar = this.resultEl.innerText.slice(-1)||'';
    this.prevKey.value = prevChar;
    this.prevKey.type = prevChar ? (this.operationValue.includes(prevChar) ? 'operator':'') : '';

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
