/*
this files is to:
1. find all elements
2. some function can be useful in others pages/script

this file must be included directly in html by <script src="js/helper.js"></script> and must be above all other js file

*/

// find all elements
const extendableElementList = document.querySelectorAll('[data-type="extendable"]');
const extendedBox = document.querySelectorAll('[data-type="extendedBox"]');

const resizers = document.querySelectorAll('.resizer');
const draggable = document.querySelectorAll('.draggable');
const calculators = document.querySelectorAll('[data-for="calculator"]');
const allSelector = document.querySelectorAll('.selector');



// ----------------------- useful functions ------------------------

//sort list by using bubble sort and return sorted list by their data-order, note this isn't very efficient
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


