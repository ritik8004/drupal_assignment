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
    var url_path = window.location.href.split('?')[0];
    e.detail.result.url_path = url_path.endsWith('/')
      ? url_path.slice(0, -1)
      : url_path;
  });
})(RcsEventManager);
