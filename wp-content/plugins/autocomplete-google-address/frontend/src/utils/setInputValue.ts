import { getInputByID } from "./getInputByID";

export function setInputValue(id: string, value: string) {
  const inputElement = getInputByID(id);
  // console.log(inputElement);

  if (inputElement) {
    inputElement.value = value;
  }
}
