/**
 * Observer for position:sticky.
 */
const stickyCTAButtonObserver = () => {
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].intersectionRatio === 0) {
      // No intersection.
      document.querySelector('.appointment-flow-action').classList.add('button-container-sticky');
    } else if (entries[0].intersectionRatio === 1) {
      // Fully intersects.
      document.querySelector('.appointment-flow-action').classList.remove('button-container-sticky');
    }
  }, {
    threshold: [0, 1],
  });

  observer.observe(document.querySelector('#appointment-bottom-sticky-edge'));
};

export { stickyCTAButtonObserver as default };
