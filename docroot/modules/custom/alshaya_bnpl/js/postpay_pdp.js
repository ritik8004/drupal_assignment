(function ($, Drupal) {
  Drupal.behaviors.postpayPDP = {
    attach: function (context, settings) {
      $('.sku-base-form').each(function () {
        setPostpayWidgetAmount(this);
      });

      $('.sku-base-form').once('postpay-pdp').on('variant-selected magazinev2-variant-selected', function (event, variant, code) {
        setPostpayWidgetAmount(this, variant, event);
      });
    }
  };

  function setPostpayWidgetAmount(element, variant, event) {
    var product = $(element).closest('[gtm-type="gtm-product-link"]');
    var sku = $(element).attr('data-sku');
    var productKey = (product.attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';
    if (typeof drupalSettings[productKey][sku] === 'undefined') {
      return;
    }
    if (typeof event !== 'undefined') {
      // We get variant details in event object for magazine v2 layout.
      if ((typeof event.detail !== 'undefined') && (typeof event.detail.variant !== 'undefined')) {
        variant = event.detail.variant;
      }
    }
    else {
      variant = $('.selected-variant-sku', element).val();
    }
    var variantPrice = (drupalSettings[productKey][sku]['type'] != 'simple') ?
      drupalSettings[productKey][sku]['variants'][variant]['gtm_price'] :
      drupalSettings[productKey][sku]['priceRaw'];

    // No need to add a condition to check if the amount is changed, Postpay
    // takes care of that.
    $('.postpay-widget', product).attr('data-amount', (variantPrice.replace(',', '') * drupalSettings.postpay.currency_multiplier).toFixed(0));
    postpay.ui.refresh();
  }
})(jQuery, Drupal);
