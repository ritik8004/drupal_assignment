/**
 * Utility function to get element value.
 */
function getElementValue(elementId) {
  return document.getElementById(elementId).value;
}

/**
 * Utility function to show inline errors in form.
 */
function showError(elementId, msg) {
  document.getElementById(elementId).innerHTML = msg;
  document.getElementById(elementId).classList.add('error');
}

/**
 * Utility function to remove inline errors in form.
 */
function removeError(elementId) {
  document.getElementById(elementId).innerHTML = '';
  document.getElementById(elementId).classList.remove('error');
}

export {
  getElementValue,
  showError,
  removeError,
};
