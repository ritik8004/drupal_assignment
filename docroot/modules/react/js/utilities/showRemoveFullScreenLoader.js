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
      if (!loaderDiv.classList.contains(contextualClass)) {
        // Add contextual class to existing loader.
        loaderDiv.classList.add(contextualClass);
      }
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
  // Populate loader div.
  const loaderDiv = document.querySelector('.fullscreen-loader');
  if (!loaderDiv) {
    return;
  }

  // Check if there is a loader with the contextual class.
  if (loaderDiv.classList.contains(contextualClass)) {
    // Remove the contextual class.
    loaderDiv.classList.remove(contextualClass);
  }

  // Check if there are still contextual classes.
  if (loaderDiv.classList.length > fullLoaderClasses.length) {
    // There are still contextual classes to be removed before we can delete it.
    return;
  }

  // Remove loader completely.
  loaderDiv.remove();
};
