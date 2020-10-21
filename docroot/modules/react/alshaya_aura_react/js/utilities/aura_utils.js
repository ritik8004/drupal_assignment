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
    document.getElementById(elementId).innerHTML = msg;
    document.getElementById(elementId).classList.add('error');
  }
}

/**
 * Utility function to remove inline errors in form.
 */
function removeError(elementId) {
  const element = document.getElementById(elementId);
  if (element) {
    document.getElementById(elementId).innerHTML = '';
    document.getElementById(elementId).classList.remove('error');
  }
}

export {
  getElementValue,
  showError,
  removeError,
};
