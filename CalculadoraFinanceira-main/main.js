class EXPANDEDBOX{
  constructor(){
    this.element = document.createElement("div");
    this.classList.add("css-expandedBox");
  }
  addNewButton(id){
    const btnMenu = document.createElement("button");
    btnMenu.id = id;
    btnMenu.classList.add("btn-menu");
    this.appendChild(btnMenu);
  }
}

function expandBox(element,target){
    element.appendChild(target);
}

const menuLeftDiv= document.getElementById("menuContainer");
const menuBox = new EXPANDEDBOX();


menuLeftDiv.addEventListener("click",event=>{
    console.log("click");
    expandBox(menuLeftDiv,menuBox);
})