export function getInputByID(id: string): HTMLInputElement | null {
  return document.getElementById(id) as HTMLInputElement;
}
