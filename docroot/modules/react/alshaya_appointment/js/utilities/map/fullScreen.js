export const requestFullscreen = (elem) => {
  elem.classList.add('fullscreen');
};

export const isFullScreen = () => (
  document.getElementsByClassName('appointment-map-view')[0].classList.contains('fullscreen')
);

/* Close fullscreen */
export const exitFullscreen = () => {
  try {
    document.getElementsByClassName('appointment-map-view')[0].classList.remove('fullscreen');
  } catch (e) {
    return false;
  }

  return true;
};
