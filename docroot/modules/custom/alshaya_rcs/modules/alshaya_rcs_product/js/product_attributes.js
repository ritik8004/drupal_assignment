/**
 * Get the product options data from the storage.
 *
 * @return {Array}
 *   Product Options data.
 */

// Load cached product options from magento backend.
(function main(RcsEventManager, Drupal, jQuery) {
  var invoked = false;

  RcsEventManager.addListener('invokingApi', function invokingApi (e) {
    if (invoked) {
      return;
    }

    var rcsType = e.request.rcsType || '';
    if (rcsType.indexOf('product') > -1) {
      invoked = true;

      e.promises.push(jQuery.ajax({
        url: Drupal.url('rcs/product-attribute-options?cacheable=1'),
        success: function success (data) {
          globalThis.RcsPhStaticStorage.set('product_options', data);
        }
      }));
    }
  });
})(RcsEventManager, Drupal, jQuery);
