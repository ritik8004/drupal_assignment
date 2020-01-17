/**
 * Detect if RTL.
 *
 * @returns {boolean}
 */
function isRTL() {
  'use strict';

  let html = document.getElementsByTagName('html')[0];
  let dir = html.getAttribute('dir');
  if (typeof dir === 'undefined' || dir === 'ltr') {
    return false;
  }
  else {
    return true;
  }
}

export {
  isRTL
};
