import smoothscroll from 'smoothscroll-polyfill';
// Use smoothscroll to fill for Safari and IE,
// Otherwise while scrollIntoView() is supported by all,
// Smooth transition is not supported apart from Chrome & FF.
smoothscroll.polyfill();

/**
 * Smooth Scroll to element in SPC.
 * @param selector
 */
export const smoothScrollTo = (selector, block) => {
  // Element exist check.
  const element = document.querySelector(selector);
  if (element === null) {
    // Scroll element not found in DOM.
    return;
  }

  // Scroll into view.
  document.querySelector(selector).scrollIntoView({
    behavior: 'smooth',
    block: (block === undefined) ? 'start' : block,
  });
};

/**
 * Smooth Scroll to error element in SPC address form.
 *
 * @param {*} element
 */
export const smoothScrollToAddressField = (element, contactField = false) => {
  let offsetPosition = 0;
  let addressOffset = 0;
  let contactHeaderOffset = 0;
  let container = document.querySelector('.spc-address-form-sidebar');
  // Check if we are in HD or CC modal.
  if (container === null || container === undefined) {
    container = document.querySelector('.spc-cnc-address-form-sidebar');
  }
  // Check if we need to scroll to contact fields.
  if (contactField === true) {
    if (document.querySelector('.delivery-address-fields') !== null
    && document.querySelector('.delivery-address-fields') !== undefined) {
      addressOffset = document.querySelector('.delivery-address-fields').offsetHeight;
    } else {
      addressOffset = document.querySelector('.store-details-wrapper').offsetHeight
        + document.querySelector('#click-and-collect-selected-store > .spc-checkout-section-title').offsetHeight;
    }
    contactHeaderOffset = document.querySelector('.spc-contact-information-header').offsetHeight;
  }
  if (window.innerWidth < 768) {
    // Header offset in mobile is section title + field height.
    const headerOffset = 56 + 45;
    const elementPosition = element.getBoundingClientRect().top;
    offsetPosition = elementPosition - headerOffset + addressOffset + contactHeaderOffset;
  } else {
    const headerOffset = 27;
    const elementPosition = element.offsetTop;
    offsetPosition = headerOffset - elementPosition + addressOffset + contactHeaderOffset;
  }
  container.scrollBy({
    top: offsetPosition,
    left: 0,
    behavior: 'smooth',
  });
};

/**
 * Smooth Scroll to active date in the mini calendar for mobile.
 */
export const smoothScrollToCurrentDate = () => {
  const element = document.querySelector('.daypicker-mobile .date-item.active');
  const elementParent = document.querySelector('.daypicker-mobile .calendar-wrapper');

  if (element !== undefined && element !== null && window.innerWidth < 768) {
    const elementLeftOffset = element.offsetLeft;
    const elementTopOffset = element.offsetTop;

    // Calculating right offset of the element for Arabic.
    const scrollRight = elementParent.offsetWidth - element.offsetWidth - elementLeftOffset;

    elementParent.scrollBy({
      top: elementTopOffset,
      left: (drupalSettings.path.currentLanguage === 'en') ? elementLeftOffset : -scrollRight,
      behavior: 'smooth',
    });
  }
};

/**
 * Smooth Scroll to active swatch for mobile.
 *
 * @param {*} element
 */
export const smoothScrollToActiveSwatch = (element) => {
  const elementParent = document.querySelector('#pdp-add-to-cart-form-main .magv2-swatch-attribute');
  const elementLeftOffset = element.offsetLeft;
  const elementTopOffset = element.offsetTop;

  // Calculating right offset of the element for Arabic.
  const scrollRight = elementParent.offsetWidth - element.offsetWidth - elementLeftOffset;
  elementParent.scrollBy({
    top: elementTopOffset,
    left: (drupalSettings.path.currentLanguage === 'en') ? elementLeftOffset : -scrollRight,
    behavior: 'smooth',
  });
};
