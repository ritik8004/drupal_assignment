/**
 * Utility function to add inline loader.
 */
export const addInlineLoader = (selector) => {
  const element = document.querySelectorAll(selector);

  if (element.length > 0) {
    element.forEach((el) => {
      el.classList.add('loading');
    });
  }
};

/**
 * Utility function to hide inline loader.
 */
export const removeInlineLoader = (selector) => {
  const element = document.querySelectorAll(selector);

  if (element.length > 0) {
    element.forEach((el) => {
      el.classList.remove('loading');
    });
  }
};
