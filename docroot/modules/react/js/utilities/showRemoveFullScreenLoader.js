const fullLoaderClasses = ['ajax-progress', 'fullscreen-loader'];

/**
 * Place ajax full screen loader.
 *
 * @param {string} contextClass
 *   The loader contextual class.
 */
export const showFullScreenLoader = (contextualClass = '') => {
  let classes = fullLoaderClasses;
  let loaderDiv = document.querySelector('.fullscreen-loader');
  if (typeof contextualClass === 'string' && contextualClass !== '') {
    if (loaderDiv) {
      if (loaderDiv.classList.contains(contextualClass)) {
        // Loader already contains this contextual class.
        return;
      }
      // Add contextual class to existing loader.
      loaderDiv.classList.add(contextualClass);
      return;
    }
    // Append contextual class to the list of classes.
    classes = fullLoaderClasses.concat([contextualClass]);
  } else if (loaderDiv) {
    // Loader already loaded.
    return;
  }

  // Create a div with the list of classes.
  loaderDiv = document.createElement('div');
  loaderDiv.className = classes.join(' ');
  document.body.appendChild(loaderDiv);
};

/**
 * Remove ajax loader.
 *
 * @param {string} context
 *   The loader context.
 */
export const removeFullScreenLoader = (contextualClass = '') => {
  let loaderDiv = null;
  if (typeof contextualClass === 'string' && contextualClass !== '') {
    // Check if there is a loader with the contextual class.
    loaderDiv = document.querySelector(`.${contextualClass}`);
    if (loaderDiv) {
      // Remove the contextual class.
      loaderDiv.classList.remove(contextualClass);
    }
  } else {
    // Populate loader div.
    loaderDiv = document.querySelector('.fullscreen-loader');
  }

  // Loader div is not on the page.
  if (!loaderDiv) {
    return;
  }

  // Check if there are still contextual classes.
  if (loaderDiv.classList.length > fullLoaderClasses.length) {
    // There are still contextual classes to be removed before we can delete it.
    return;
  }

  // Remove loader completely.
  loaderDiv.remove();
};
