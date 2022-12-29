/**
 * Detect browser is modern or not and add variable to window object
 */
(function (Drupal) {
  Drupal.isModernBrowser = function () {
    try {
      () => { };

      // Class support
      class __ES6Test { }

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

      return { isModernBrowser: true };
    } catch (error) {
      return {
        isModernBrowser: false,
        isModernBrowserError: error.name !== undefined && error.message !== undefined ? error.name + ': ' + error.message : error,
      };
    }
  };
})(Drupal);
