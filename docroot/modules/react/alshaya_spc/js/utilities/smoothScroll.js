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
export const smoothScrollToAddressField = (element, contactField = false) => {
  const container = document.querySelector('.spc-address-form-sidebar');
  let offsetPosition = 0;
  let addressOffset = 0;
  let contactheaderOffset = 0;
  if (contactField === true) {
    addressOffset = document.querySelector('.delivery-address-fields').offsetHeight;
    contactheaderOffset = document.querySelector('.spc-contact-information-header').offsetHeight;
  }
  if (window.innerWidth < 768) {
    // Header offset in mobile is section title + field height.
    const headerOffset = 56 + 45;
    const elementPosition = element.getBoundingClientRect().top;
    offsetPosition = elementPosition - headerOffset + addressOffset + contactheaderOffset;
  } else {
    const headerOffset = 27;
    const elementPosition = element.offsetTop;
    offsetPosition = headerOffset - elementPosition + addressOffset + contactheaderOffset;
  }
  container.scrollBy({
    top: offsetPosition,
    left: 0,
    behavior: 'smooth',
  });
};
