import smoothscroll from 'smoothscroll-polyfill';
// Use smoothscroll to fill for Safari and IE,
// Otherwise while scrollIntoView() is supported by all,
// Smooth transition is not supported apart from Chrome & FF.
smoothscroll.polyfill();

/**
 * Smooth Scroll to element in SPC.
 * @param selector
 */
const smoothScrollTo = (selector) => {
  document.querySelector(selector).scrollIntoView({
    behavior: 'smooth',
  });
};

export default smoothScrollTo;
