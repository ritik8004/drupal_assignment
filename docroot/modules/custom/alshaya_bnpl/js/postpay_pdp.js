(function ($, Drupal) {
  Drupal.behaviors.postpayPDP = {
    attach: function (context, settings) {
      var skuBaseForm = $('.sku-base-form').not('[data-sku *= "#"]');

      document.addEventListener('alshayaPostpayInit', () => {
        skuBaseForm.each(function () {
          setPostpayWidgetAmount(this);
        });

        skuBaseForm.once('postpay-pdp').on('variant-selected magazinev2-variant-selected', function (event, variant, code) {
          setPostpayWidgetAmount(this, variant, event);
        });
      });
    }
  };

  function setPostpayWidgetAmount(element, variant, event) {
    var product = $(element).closest('[gtm-type="gtm-product-link"]');
    var sku = $(element).attr('data-sku');
    var productKey = (product.attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';
    var productData = window.commerceBackend.getProductData(sku, productKey);

    if (productData === null) {
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
      // Check if we are in mag-v2 layout
      // due to different markup the initial variant fetch will fail.
      if (typeof variant === 'undefined') {
        if ($('body').hasClass('magazine-layout-v2')) {
          // variantselected is an attribute in magv2 form.
          variant = $(element).attr('variantselected');
        }
      }
    }
    // @todo Check this works for all kinds of products:
    // simple, simple grouped, configurable, configurable grouped and matchback.
    var variantPrice = (productData.type != 'simple') ?
      productData['variants'][variant]['gtm_price'] :
      productData.gtm_attributes.price;

    // No need to add a condition to check if the amount is changed, Postpay
    // takes care of that.
    $('.postpay-widget', product).attr('data-amount', (variantPrice.replace(',', '') * drupalSettings.postpay.currency_multiplier).toFixed(0));
    postpay.ui.refresh();
  }
})(jQuery, Drupal);
