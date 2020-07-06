const getStringMessage = (key, replacement) => {
  try {
    const element = document.querySelector(`[data-string-id="${key}"]`);
    if (element !== undefined && element !== null) {
      const str = element.value.toString();
      return replacement ? Drupal.formatString(str, replacement) : str;
    }
  } catch (e) {
    Drupal.logJavascriptError('getStringMessage fail', e);
  }

  return '';
};

export default getStringMessage;
