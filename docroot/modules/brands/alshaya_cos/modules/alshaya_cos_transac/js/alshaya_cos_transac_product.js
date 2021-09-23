/**
 * Listens to the 'alshayaRcsUpdateResults' event and updated the result object.
 */
(function main($) {

  $(document).ready(function ready() {
    // Event listener to update the data layer object with the proper category
    // data.
    document.addEventListener('alshayaRcsUpdateResults', (e) => {
      // Return if result is empty.
      if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'product') {
        return null;
      }
      console.log('Do something here');
    });
  });
})(jQuery);
