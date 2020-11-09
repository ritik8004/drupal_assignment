(function ($, Drupal, drupalSettings) {
  'use strict';

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
      data = [{
        code: currentSelectedVariant,
        quantity: quantity,
        amount: price * quantity,
      }];
    }

    return { data, context };
  };

  Drupal.dispatchProductUpdateEvent = function (element) {
    var data = Drupal.getSelectedVariantDetails(element);
    const event = new CustomEvent('productUpdate', {
      bubbles: true,
      detail: data,
    });
    document.querySelector('.sku-base-form').dispatchEvent(event);
  };

})(jQuery, Drupal, drupalSettings)

