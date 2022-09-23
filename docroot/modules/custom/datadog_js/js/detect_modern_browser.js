/**
 * @file
 * Detect modern browser utility.
 */
// Adding global flag to detect modern browser
(function (window) {
  window.isModernBrowser = false;
})(window);

// Adding logic to detect modern ES6 js is supported
(function (window) {
  () => {};

  // Class support
  class __ES6Test {}

  // Object initializer property and method shorthands
  let a = true;
  let b = {
    a,
    c() {
      return true;
    },
    d: [1, 2, 3],
  };
  const g = true;

  // Object destructuring
  let { c, d } = b;

  // Spread operator
  let e = [...d, 4];

  window.isModernBrowser = true;
})(window);
