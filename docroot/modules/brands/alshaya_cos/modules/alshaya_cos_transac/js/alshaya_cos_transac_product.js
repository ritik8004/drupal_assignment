/**
 * Listens to the 'alshayaRcsUpdateResults' event and updated the result object.
 */
(function main($) {
  // Event listener to update the data layer object with the proper category
  // data.
  document.addEventListener('alshayaRcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'product') {
      return;
    }

    var data = e.detail.result;

    // Title/description brand overrides. See CORE-34549.
    var e.detail.result.description.html = data.description.html
      + data.composition
      + data.washing_instructions
      + data.article_warning
      + 'Make sure that your favourite item remain...'
      + data.sku;

    var e.detail.result.short_description;

    // @todo add image brand overrides. See CORE-34424.
  });
})(jQuery);
