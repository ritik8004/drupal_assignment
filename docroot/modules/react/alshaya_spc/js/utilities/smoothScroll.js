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
  let container = document.querySelector('.spc-address-form-wrapper');
  let homeDelivery = false;
  // Check if we are in HD or CC modal.
  if (container === null || container === undefined) {
    // If we are in mobile the scroll is on popup.
    if (window.innerWidth < 768) {
      container = document.querySelector('.popup-content');
    } else {
      container = document.querySelector('.spc-cnc-selected-store-content');
    }
  }
  // Check if we need to scroll to contact fields.
  if (contactField === true) {
    if (document.querySelector('.delivery-address-fields') !== null
    && document.querySelector('.delivery-address-fields') !== undefined) {
      addressOffset = 0;
      homeDelivery = true;
    } else {
      addressOffset = document.querySelector('.store-details-wrapper').offsetHeight
        + document.querySelector('#click-and-collect-selected-store > .spc-cnc-selected-store-header').offsetHeight;
      contactHeaderOffset = document.querySelector('.spc-contact-information-header').offsetHeight;
    }
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

  // Temp solution to scroll for home delivery modal without map.
  if (homeDelivery === true) {
    offsetPosition = -300;
  }
  container.scrollBy({
    top: offsetPosition,
    left: 0,
    behavior: 'smooth',
  });
};
