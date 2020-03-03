/**
 * Detect if RTL.
 *
 * @returns {boolean}
 */
function isRTL() {
  const html = document.getElementsByTagName('html')[0];
  const dir = html.getAttribute('dir');
  if (typeof dir === 'undefined' || dir === 'ltr') {
    return false;
  }

  return true;
}

export {
  isRTL,
};
