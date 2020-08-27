const setupAccordionHeight = (ref) => {
  if (ref.current !== null) {
    const element = ref.current;
    element.style.maxHeight = `${ref.current.offsetHeight}px`;
  }
};

export default setupAccordionHeight;
