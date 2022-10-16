/**
 * Listens to the 'rcsUpdateResults' event and updated the result object.
 */
(function main(Drupal) {
  // Event listener to update the url key with the proper super cateogory url.

  RcsEventManager.addListener('rcsUpdateResults', (e) => {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined') {
      return;
    }

    // If the result is array then process all the items.
    if (Array.isArray(e.detail.result)) {
      e.detail.result.forEach(item => {
        if (Drupal.hasValue(item.end_user_url)) {
          var endUserUrl = item.end_user_url.replace('.html', '');
          item.url_key = endUserUrl;
        }
      });
    } else if (Drupal.hasValue(e.detail.result.end_user_url)) {
      // Filter out the .html from the `end_user_url`.
      var endUserUrl = e.detail.result.end_user_url.replace('.html', '');
      e.detail.result.url_key = endUserUrl;
    }
  });
})(Drupal);
