/**
 * Place ajax full screen loader.
 */
export const showFullScreenLoader = () => {
  const loaderDivExisting = document.getElementsByClassName('ajax-progress-fullscreen');
  if (loaderDivExisting.length > 0) {
    return;
  }

  const loaderDiv = document.createElement('div');
  loaderDiv.className = 'ajax-progress ajax-progress-fullscreen';
  document.body.appendChild(loaderDiv);
};

/**
 * Remove ajax loader.
 */
export const removeFullScreenLoader = () => {
  const loaderDiv = document.getElementsByClassName('ajax-progress-fullscreen');
  while (loaderDiv.length > 0) {
    loaderDiv[0].parentNode.removeChild(loaderDiv[0]);
  }
};
