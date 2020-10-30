const getStringMessage = (key, replacement) => {
  try {
    const element = document.querySelector(`[data-string-id="${key}"]`);
    if (element !== undefined && element !== null) {
      const str = element.value.toString();
      return replacement ? Drupal.formatString(str, replacement) : str;
    }
  } catch (e) {
    Drupal.logJavascriptError('Error occurred in getStringMessage', e.message);
  }

  return '';
};

export default getStringMessage;
