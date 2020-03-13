export const getStringMessage = function (key) {
  try {
    const element = document.querySelector(`[data-string-id="${key}"]`);
    if (element !== undefined && element !== null) {
      return element.value.toString();
    }
  } catch (e) {
  }

  return '';
};
