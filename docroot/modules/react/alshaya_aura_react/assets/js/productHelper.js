(function ($, Drupal, drupalSettings) {
  'use strict';

  // Helper to prepare total price of the selected variant considering the quantity.
  Drupal.getSelectedVariantDetails = function (element) {
    var data = []
    var form = element.parents('form');
    var node = element.parents('.entity--type-node');
    var currentSelectedVariant = $('[name="selected_variant_sku"]', form).val();
    var sku = $(node).attr('data-sku');

    if (currentSelectedVariant !== '') {
      var quantity = $('[name="quantity"]', form).val() || 1;
      var viewMode = $(node).attr('data-vmode');
      var productKey = (viewMode === 'matchback')
        ? 'matchback'
        : 'productInfo';
      var context = (viewMode === 'full')
        ? 'main'
        : 'related';
      var variantInfo = drupalSettings[productKey][sku]['variants'][currentSelectedVariant];
      var price = variantInfo ? variantInfo.priceRaw : 0;
      data = {
        amount: price * quantity,
      };
    }

    return { data, context };
  };

  // Event dispatcher on variant/quantity change to update aura accrual points. 
  Drupal.dispatchProductUpdateEvent = function (element) {
    var data = Drupal.getSelectedVariantDetails(element);
    const event = new CustomEvent('auraProductUpdate', {
      bubbles: true,
      detail: data,
    });
    document.querySelector('.sku-base-form').dispatchEvent(event);
  };

})(jQuery, Drupal, drupalSettings)

