/**
 * Handles sticky item on basket and checkout pages.
 */

/**
 * Helper function to get real offsets.
 *
 * @param element
 * Element to fetch offsets for.
 *
 * @returns {{top: number, left: number}}
 */
function getPosition (element) {
  let clientRect = element.getBoundingClientRect();
  return {left: clientRect.left + document.body.scrollLeft,
    top: clientRect.top + document.body.scrollTop};
}

/**
 * Helper to get Sidebar Offset based on page.
 */
function getSiderBarOffsetTop() {
  let offSet = 0;
  let preContentOffset = 0;
  preContentOffset = document.getElementsByClassName('spc-pre-content')[0].offsetHeight === 0 ? 0 : document.getElementsByClassName('spc-pre-content')[0].offsetHeight + 20;
  if (document.getElementsByClassName('page-standard')[0].classList.contains('spc-checkout-sticky-sidebar')) {
    offSet = document.getElementsByClassName('site-brand-wrapper')[0].offsetHeight
      + document.getElementById('block-page-title').offsetHeight
      + preContentOffset;
  }
  else {
    offSet = document.getElementsByClassName('header--wrapper')[0].offsetHeight
      + document.getElementsByClassName('branding__menu')[0].offsetHeight
      + document.getElementsByClassName('c-breadcrumb')[0].offsetHeight
      + preContentOffset
      + 40;
  }

  return offSet;
}

/**
 * Mobile cart header preview sticky.
 */
function stickyMobileCartPreview () {
  window.addEventListener('scroll', () => {
    // Mobile cart sticky header.
    if (window.innerWidth < 768) {
      let cartPreview = document.getElementsByClassName('spc-mobile-cart-preview');
      let cartPreviewOffset = getPosition(cartPreview[0]);
      if (window.pageYOffset > cartPreviewOffset.top) {
        if (!cartPreview[0].classList.contains('sticky')) {
          cartPreview[0].classList.add('sticky');
          document.getElementsByClassName('spc-main')[0].style.paddingTop = cartPreview[0].offsetHeight + 'px';
        }
      }
      else {
        cartPreview[0].classList.remove('sticky');
        document.getElementsByClassName('spc-main')[0].style.paddingTop = 0;
      }
    }
  });
}

/**
 * Sticky SPC sidebar.
 */
function stickySidebar() {
  window.addEventListener('scroll', () => {
    // Desktop & Tablet sticky right column.
    if (window.innerWidth > 767) {
      // Before we begin we need to check if the content is smaller than sidebar,
      // if yes no sticky needed.
      // 40 is margin bottom which is fixed.
      let spcPromoCodeBlockH = document.getElementsByClassName('spc-promo-code-block')[0].offsetHeight + 40;
      let orderSummaryBlockH = document.getElementsByClassName('spc-order-summary-block')[0].offsetHeight;
      // 42 is height of section title for cart items.
      let cartItemsH = document.getElementsByClassName('spc-cart-items')[0].offsetHeight + 42;
      if (cartItemsH < spcPromoCodeBlockH + orderSummaryBlockH) {
        // Content not eligible for sticky.
        return;
      }

      let offSet = getSiderBarOffsetTop();
      // Sidebar.
      let spcSidebar = document.getElementsByClassName('spc-sidebar');
      let spcSidebarWidth = spcSidebar[0].offsetWidth;
      let spcSidebarOffset = getPosition(spcSidebar[0]);
      let spcSideBarBottom = spcSidebarOffset.top + spcSidebar[0].offsetHeight;

      // SPC Content.
      let spcMainContent = document.getElementsByClassName('spc-content');
      let spcMainContentOffset = getPosition(spcMainContent[0]);
      let spcMainContentBottom = spcMainContentOffset.top + spcMainContent[0].offsetHeight;

      let sidebarStickyTop = spcMainContentBottom - spcSidebar[0].offsetHeight;

      // When the sidebar becomes sticky.
      if (window.pageYOffset > offSet) {
        if (!spcSidebar[0].classList.contains('sticky')) {
          spcSidebar[0].style.width = spcSidebarWidth + 'px';
          if (!spcSidebar[0].classList.contains('fluid')) {
            spcSidebar[0].style.left = spcSidebarOffset.left + 'px';
          }
          spcSidebar[0].classList.add('sticky');
        }

        // When sticky but content bottom is reached and footer overlap
        // is imminent.
        if (spcMainContentBottom < spcSideBarBottom) {
          if (!spcSidebar[0].classList.contains('fluid')) {
            spcSidebar[0].classList.add('fluid');
            spcSidebar[0].style.left = '';
          }
        }
        else {
          if (sidebarStickyTop > 35.2) {
            spcSidebar[0].style.left = spcSidebarOffset.left + 'px';
            spcSidebar[0].classList.remove('fluid');
          }
        }
      }
      else {
        spcSidebar[0].classList.remove('sticky');
        spcSidebar[0].style.left = '';
        spcSidebar[0].style.width = '';
      }
    }
  });
}

export {
  stickyMobileCartPreview,
  stickySidebar
};
