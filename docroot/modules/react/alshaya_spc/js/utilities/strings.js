import logger from '../../../js/utilities/logger';

const getStringMessage = (key, replacement) => {
  try {
    const element = document.querySelector(`[data-string-id="${key}"]`);
    if (element !== undefined && element !== null) {
      const str = element.value.toString();
      return replacement ? Drupal.formatString(str, replacement) : str;
    }
  } catch (error) {
    logger.error('Error occurred in getStringMessage.', {
      string: key,
      error,
    });
  }

  return '';
};

export default getStringMessage;
