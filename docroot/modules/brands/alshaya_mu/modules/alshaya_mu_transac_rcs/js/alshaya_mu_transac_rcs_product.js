/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
 (function main() {
  // Event listener to update the data layer object with the proper product
  // data.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'product') {
      return;
    }

    var data = e.detail.result;
    // @todo work on short description.

  });
})();
