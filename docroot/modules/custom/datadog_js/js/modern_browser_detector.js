/**
 * Detect browser is modern or not and add variable to window object
 */
(function () {
  window.isModernBrowser = false;
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

		window.isModernBrowser = true;
	} catch (error) {
		window.isModernBrowser = false;
	}
}());
