/**
 * Get the product options data from the storage.
 *
 * @return {Array}
 *   Product Options data.
 */

// Load cached product options from magento backend.
(function main(RcsEventManager, Drupal, jQuery) {
  RcsEventManager.addListener('invokingApi', function invokingApi (e) {
    var rcsType = e.request.rcsType || '';
    if (rcsType === 'product') {
      e.promises.push(jQuery.ajax({
        url: Drupal.url('rcs/product-attribute-options'),
        success: function success (data) {
          globalThis.RcsPhStaticStorage.set('product_options', {
            data: {
              customAttributeMetadata: data,
            }
          });
        }
      }));
    }
  });
})(RcsEventManager, Drupal, jQuery);
