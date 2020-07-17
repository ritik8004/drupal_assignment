import smoothscroll from 'smoothscroll-polyfill';
// Use smoothscroll to fill for Safari and IE,
// Otherwise while scrollIntoView() is supported by all,
// Smooth transition is not supported apart from Chrome & FF.
smoothscroll.polyfill();

/**
 * Smooth Scroll to element.
 */
function smoothScrollTo(selector, block) {
  document.querySelector(selector).scrollIntoView({
    behavior: 'smooth',
    block: (block === undefined) ? 'start' : block,
  });
}

export default smoothScrollTo;
