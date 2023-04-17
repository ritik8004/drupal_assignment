/**
 * Handles sticky item on basket and checkout pages.
 */

/**
 * Mobile cart header preview sticky.
 */
function stickyMobileCartPreview() {
  const cartPreview = document.getElementsByClassName('spc-mobile-cart-preview');
  // If for some reason block is not available, dont proceed.
  if (cartPreview.length === 0) {
    return;
  }

  // Check for super category menu.
  const superCategoryMenu = document.getElementsByClassName('block-alshaya-super-category-menu')[0];

  // Check for super menu.
  const superMenu = document.getElementById('block-supermenu');

  // Check branding menu height.
  const brandingMenuHeight = document.getElementsByClassName('branding__menu')[0].offsetHeight || 0;

  let menuHeight = 0;

  // SPC Cart Preview offset.
  const cartPreviewOffset = cartPreview[0].offsetHeight / 1.75;

  // SPC-Pre-Content offset.
  // Content might come via AJAX.
  const preContentHeight = document.getElementsByClassName('spc-pre-content')[0].offsetHeight;

  // Breadcrumb offset.
  const breadCrumbHeight = document.getElementsByClassName('c-breadcrumb')[0].offsetHeight;

  // Menu offset.
  if (superCategoryMenu && superCategoryMenu.offsetHeight) {
    // In super category menu, we allow the super category menu to scroll after
    // minimalistic header, hence factor only menu nav bar height.
    menuHeight = document.getElementById('block-mobilenavigation').offsetHeight;
  } else if (superMenu && superMenu.offsetHeight) {
    menuHeight = superMenu.offsetHeight + brandingMenuHeight;
  } else {
    menuHeight = brandingMenuHeight;
  }

  window.addEventListener('scroll', () => {
    // Mobile cart sticky header.
    if (window.innerWidth < 768) {
      if (cartPreview[0]) {
        const cartOffsetTop = menuHeight + breadCrumbHeight + preContentHeight - cartPreviewOffset;
        if (window.pageYOffset > cartOffsetTop) {
          if (!cartPreview[0].classList.contains('sticky')) {
            cartPreview[0].classList.add('sticky');
            document.getElementsByClassName('spc-main')[0].style.paddingTop = `${cartPreview[0].offsetHeight}px`;
            cartPreview[0].style.top = `${menuHeight}px`;
          }
        } else {
          cartPreview[0].classList.remove('sticky');
          document.getElementsByClassName('spc-main')[0].style.paddingTop = 0;
          cartPreview[0].style.top = 0;
        }
      } else {
        Drupal.alshayaLogger('warning', 'sticky cart preview failure', 'Cart preview element not found');
      }
    }
  });
}

export default stickyMobileCartPreview;
