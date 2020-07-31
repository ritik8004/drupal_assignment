export const setupAccordionHeight = (ref) => {
  const maxHeight = `${ref.current.offsetHeight}px`;
  ref.current.setAttribute('data-max-height', maxHeight);
  ref.current.classList.add('max-height-processed');
};

export const allowMaxContent = (ref) => {
  ref.current.classList.add('max-height-allowed');
};

export const removeMaxHeight = (ref) => {
  ref.current.classList.remove('max-height-allowed');
};
