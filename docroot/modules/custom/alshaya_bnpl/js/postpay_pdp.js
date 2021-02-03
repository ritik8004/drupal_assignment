(function ($, Drupal) {
  Drupal.behaviors.postpayPDP = {
    attach: function (context, settings) {
      $('.sku-base-form').once('postpay-pdp').on('variant-selected magazinev2-variant-selected', function (event, variant, code) {
        var product = $(this).closest('[gtm-type="gtm-product-link"]');
        var sku = $(this).attr('data-sku');
        var productKey = (product.attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';
        if (typeof drupalSettings[productKey][sku] === 'undefined') {
          return;
        }

        // We get variant details in event object for magazine v2 layout.
        if ((typeof event.detail !== 'undefined') && (typeof event.detail.variant !== 'undefined')) {
          variant = event.detail.variant;
        }
        var variantInfo = drupalSettings[productKey][sku]['variants'][variant];

        $('.postpay-widget').attr('data-amount', variantInfo['gtm_price'] * 100);
        postpay.ui.refresh();
      });
    }
  };

})(jQuery, Drupal);
