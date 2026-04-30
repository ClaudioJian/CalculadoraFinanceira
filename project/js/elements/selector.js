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

  for(let s of allSelector){
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
      for(let c of targetBox.children){
        if(c.dataset.type!=='all') c.classList.add('hidden')
      };
      
      if(s.dataset.filterid) ClearDepedentSelector(s.dataset.filterid);
    }
  }

}

const AddSelectEvent = (parentElement,itemBox)=>{
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

const GetDepencityTarget = (itemBox)=>{
  const depencityID = itemBox.dataset.depencityid;

  for(let s of allSelector){
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

export {AddSelectEvent, GetDepencityTarget};