/**
 * Utility function to get element value.
 */
function getElementValue(elementId) {
  const elementValue = document.getElementById(elementId)
    ? document.getElementById(elementId).value
    : '';
  return elementValue;
}

/**
 * Utility function to show inline errors in form.
 */
function showError(elementId, msg) {
  const element = document.getElementById(elementId);
  if (element) {
    element.innerHTML = msg;
    element.classList.add('error');
  }
}

/**
 * Utility function to remove inline errors in form.
 */
function removeError(elementId) {
  const element = document.getElementById(elementId);
  if (element) {
    element.innerHTML = '';
    element.classList.remove('error');
  }
}

/**
 * Utility function to get aura localStorage key.
 */
function getAuraLocalStorageKey() {
  return 'aura_data';
}

export {
  getElementValue,
  showError,
  removeError,
  getAuraLocalStorageKey,
};
