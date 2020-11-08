(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.getSelectedVariantDetails = function (element) {
    var data = []
    var elementNode = $('[name="' + element +'"]');
    var form = elementNode.parents('form');
    var node = elementNode.parents('.entity--type-node');

    var currentSelectedVariant = $('[name="selected_variant_sku"]', form).val();
    var sku = $(node).attr('data-sku');

    if (currentSelectedVariant !== '') {
      var quantity = $('[name="quantity"]', form).val() || 1;
      var viewMode = $(node).attr('data-vmode');
      var productKey = (viewMode === 'matchback')
        ? 'matchback'
        : 'productInfo';
      var variantInfo = drupalSettings[productKey][sku]['variants'][currentSelectedVariant];
      var price = variantInfo ? variantInfo.priceRaw : 0;
      data = [{
        code: currentSelectedVariant,
        quantity: quantity,
        amount: price * quantity,
      }];
    }

    return data;
  };

})(jQuery, Drupal, drupalSettings)

