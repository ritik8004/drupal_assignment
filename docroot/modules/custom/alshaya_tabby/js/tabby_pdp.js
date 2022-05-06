(function ($, Drupal) {
  Drupal.behaviors.tabbyPDP = {
    attach: function (context) {
      $('.sku-base-form', context).once('tabby-pdp').each(function () {
        setTabbyWidgetAmount(this);
      });

      $('.sku-base-form', context).once('tabby-pdp-variant').on('variant-selected magazinev2-variant-selected', function (event, variant, code) {
        setTabbyWidgetAmount(this, variant, event);
      });
    }
  };

  function setTabbyWidgetAmount(element, variant, event) {
    var product = $(element).closest('[gtm-type="gtm-product-link"]');
    var sku = $(element).attr('data-sku');
    var productKey = (product.attr('data-vmode') === 'matchback') ? 'matchback' : 'productInfo';
    var productData = window.commerceBackend.getProductData(sku, productKey, true);

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
    var variantPrice = (productData['type'] !== 'simple' && variant) ?
      productData['variants'][variant]['gtm_price'] :
      productData['gtm_attributes']['price'];

    // Check if the amount is invalid.
    if (typeof variantPrice === 'undefined' || !(variantPrice)) {
      Drupal.alshayaLogger('warning', 'Invalid amount on PDP page for tabby. SKU: @sku, Variant SKU: @variant', {
        '@sku': sku,
        '@variant': variant,
      });
      return;
    }

    // Tabby promo change event.
    const tabbyWidget = $(element).closest('.entity--type-node').find('.' + drupalSettings.tabby.widgetInfo.class);
    tabbyWidget.each(function () {
      const selector = $(this).attr('id');
      if (selector !== undefined) {
        Drupal.tabbyPromoInit('#' + selector, variantPrice.replace(',', ''), 'product');
      }
    });
  }
})(jQuery, Drupal);
