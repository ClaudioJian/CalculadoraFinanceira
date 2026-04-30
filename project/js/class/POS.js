export class POS {
  constructor(targetElement,resizedSize={}) {
    this.top = targetElement.style.top;
    this.left = targetElement.style.left;
    this.width = targetElement.style.width;
    this.height = targetElement.style.height;
    this.resizedSize = resizedSize;
  }
}