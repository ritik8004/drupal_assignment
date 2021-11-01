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
      // Check if we are in mag-v2 layout
      // due to different markup the initial variant fetch will fail.
      if (typeof variant === 'undefined') {
        if ($('body').hasClass('magazine-layout-v2')) {
          // variantselected is an attribute in magv2 form.
          variant = $(element).attr('variantselected');
        }
      }
    }
    var variantPrice = (drupalSettings[productKey][sku]['type'] !== 'simple') ?
      drupalSettings[productKey][sku]['variants'][variant]['gtm_price'] :
      drupalSettings[productKey][sku]['gtm_attributes']['price'];

    // Tabby promo change event.
    new TabbyPromo({
      selector: '#' + drupalSettings.tabby.selector,
      currency: drupalSettings.alshaya_spc.currency_config.currency_code,
      price: variantPrice,
      installmentsCount: drupalSettings.tabby.tabby_installment_count,
      lang: drupalSettings.tabby.locale,
      source: 'product',
      api_key: drupalSettings.tabby.public_key
    });
  }
})(jQuery, Drupal);
