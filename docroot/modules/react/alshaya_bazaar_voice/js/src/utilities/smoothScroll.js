import smoothscroll from 'smoothscroll-polyfill';
// Use smoothscroll to fill for Safari and IE,
// Otherwise while scrollIntoView() is supported by all,
// Smooth transition is not supported apart from Chrome & FF.
smoothscroll.polyfill();

/**
 * Smooth Scroll to element in Reviews and Rating.
 * @param e
 * @param selector
 * @param request
 * @param context
 * @param behavior
 */
export function smoothScrollTo(e, selector, request, context, behavior) {
  if (request === 'post_review') {
    // Prevents React from resetting its properties.
    e.persist();
  } else {
    e.preventDefault();
  }
  // Scroll to error field.
  const elementId = document.querySelector(selector).id;
  let element = selector;
  if (elementId !== null && elementId !== '' && context === 'write_review') {
    if (elementId === 'rating-error') {
      element = (request) ? '.product-title' : '#rating';
    } else {
      const parentId = document.getElementById(elementId).parentElement.id;
      if (parentId !== '') {
        element = `#${document.getElementById(parentId).previousElementSibling.id}`;
      }
    }
  }

  // If behavior is specified as 'auto' then use the default behavior else
  // use the 'smooth' behavior.
  document.querySelector(element).scrollIntoView({
    behavior: behavior || 'smooth',
  });
}

export default {
  smoothScrollTo,
};
