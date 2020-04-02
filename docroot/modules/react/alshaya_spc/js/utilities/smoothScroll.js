import smoothscroll from 'smoothscroll-polyfill';
// Use smoothscroll to fill for Safari and IE,
// Otherwise while scrollIntoView() is supported by all,
// Smooth transition is not supported apart from Chrome & FF.
smoothscroll.polyfill();

/**
 * Smooth Scroll to element in SPC.
 * @param selector
 */
export const smoothScrollTo = (selector) => {
  document.querySelector(selector).scrollIntoView({
    behavior: 'smooth',
  });
};

/**
 * Smooth Scroll to error element in SPC address form.
 *
 * @param {*} element
 */
export const smoothScrollToAddressField = (element) => {
  const container = document.querySelector('.spc-address-form-sidebar');
  if (window.innerWidth < 768) {
    // Header offset in mobile is section title + field height.
    const headerOffset = 56 + 45;
    const elementPosition = element.getBoundingClientRect().top;
    const offsetPosition = elementPosition - headerOffset;
    container.scrollBy({
      top: offsetPosition,
      left: 0,
      behavior: 'smooth',
    });
  } else {
    const headerOffset = 27;
    const elementPosition = element.offsetTop;
    const offsetPosition = headerOffset - elementPosition;
    container.scrollBy({
      top: offsetPosition,
      left: 0,
      behavior: 'smooth',
    });
  }
};
