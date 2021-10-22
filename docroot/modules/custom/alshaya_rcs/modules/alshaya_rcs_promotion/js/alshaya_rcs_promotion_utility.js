/**
 * Listens to the 'alshayaRcsUpdateResults' event and updated the result object.
 */
(function main($) {
  // Event listener to update the data layer object with the proper category
  // data.
  RcsEventManager.addListener('alshayaRcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'promotion') {
      return null;
    }
    // Adding name in place of title so that RCS replace the placeholder
    // properly.
    e.detail.result.name = e.detail.result.title;
  });
})(jQuery);
