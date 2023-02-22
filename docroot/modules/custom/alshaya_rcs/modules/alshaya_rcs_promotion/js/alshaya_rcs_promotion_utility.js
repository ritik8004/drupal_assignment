/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main(RcsEventManager) {
  // Event listener to update the data layer object with the proper category
  // data.
  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined' || e.detail.pageType !== 'promotion') {
      return null;
    }
    // Adding name in place of title so that RCS replace the placeholder
    // properly.
    e.detail.result.name = e.detail.result.title;
    e.detail.result.url_path = window.location.href.replace(/\/$/g, '').split('?')[0];
  });
})(RcsEventManager);
