/**
 * @file
 * Utils.
 */

/* eslint-disable */
function isRTL() {
/* eslint-enable */

  var html = jQuery('html');
  var dir = html.attr('dir');
  if (typeof dir === 'undefined' || dir === 'ltr') {
    return false;
  }
  else {
    return true;
  }
}

/* eslint-disable */
function debounce(func, wait, immediate) {
/* eslint-enable */

  var timeout;

  return function () {
    var context = this;
    var args = arguments;
    var later = function () {
      timeout = null;
      if (!immediate) {
        func.apply(context, args);
      }
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) {
      func.apply(context, args);
    }
  };
}
