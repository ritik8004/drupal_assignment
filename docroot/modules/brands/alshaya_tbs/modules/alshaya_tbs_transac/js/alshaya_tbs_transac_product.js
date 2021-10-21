/**
 * Listens to the 'alshayaRcsUpdateResults' event and updated the result object.
 */
(function main($) {
  // Event listener to update the data layer object with the proper category
  // data.
  window.EventManager.addListener('alshayaRcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'product') {
      return;
    }
    // @todo add title/description overrides. See CORE-34431.
  });
})(jQuery);
