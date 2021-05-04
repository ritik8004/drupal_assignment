import smoothscroll from 'smoothscroll-polyfill';
// Use smoothscroll to fill for Safari and IE,
// Otherwise while scrollIntoView() is supported by all,
// Smooth transition is not supported apart from Chrome & FF.
smoothscroll.polyfill();

/**
 * Smooth Scroll to element in Reviews and Rating.
 * @param selector
 */
export default function smoothScrollTo(e, selector, event) {
  if (event === 'post_review') {
    // Prevents React from resetting its properties:
    e.persist();
  } else {
    e.preventDefault();
  }

  document.querySelector(selector).scrollIntoView({
    behavior: 'smooth',
  });
}
