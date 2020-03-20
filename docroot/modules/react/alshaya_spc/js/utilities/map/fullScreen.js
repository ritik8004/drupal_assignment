export const requestFullscreen = (elem) => {
  if (elem.requestFullscreen) {
    elem.requestFullscreen();
  } else if (elem.mozRequestFullScreen) { // Firefox.
    elem.mozRequestFullScreen();
  } else if (elem.webkitRequestFullscreen) { // Chrome, Safari and Opera.
    elem.webkitRequestFullscreen();
  } else if (elem.msRequestFullscreen) { // IE/Edge.
    elem.msRequestFullscreen();
  }
};

export const isFullScreen = () => (
  document.fullscreenElement // Standard syntax.
  || document.webkitFullscreenElement // Chrome, Safari and Opera syntax.
  || document.mozFullScreenElement // Firefox syntax
  || document.msFullscreenElement
);

/* Close fullscreen */
export const exitFullscreen = () => {
  if (document.mozCancelFullScreen) { // Firefox.
    return document.mozCancelFullScreen();
  }
  if (document.webkitExitFullscreen) { // Chrome, Safari and Opera.
    return document.exitFullscreen();
  }
  if (document.msExitFullscreen) { // IE/Edge.
    return document.msExitFullscreen();
  }
  return document.exitFullscreen();
};
