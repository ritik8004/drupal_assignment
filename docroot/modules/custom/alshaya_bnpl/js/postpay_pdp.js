(function ($, Drupal) {
  // Flag to check if Postpay is initialized or not.
  var postpayInitialized = false;
  // Updates the flag variable once Postpay is initialized.
  document.addEventListener('alshayaPostpayInit', () => {
    postpayInitialized = true;

    // We trigger Drupal behavior here so that in case Postpay was initialized
    // after the behaviors are finished executing, then this will take care of
    // executing the behavior code again.
    Drupal.behaviors.postpayPDP.attach(document);
  });

  Drupal.behaviors.postpayPDP = {
    attach: function (context, settings) {
      if (!postpayInitialized) {
        return;
      }

      var skuBaseForm = $('.sku-base-form', context).not('[data-sku *= "#"]');
      // Proceed if '.sku-base-form' exists.
      if (skuBaseForm.length <= 0) {
        return;
      }
      var selectedVariant = null;

      skuBaseForm.once('postpay-pdp-initial').each(function () {
        setPostpayWidgetAmount(this);
      });

      skuBaseForm.once('postpay-pdp').on('variant-selected magazinev2-variant-selected', function (event, variant, code) {
        selectedVariant = variant;
        setPostpayWidgetAmount(this, variant, event);
      });

      // Set the amount and render the postpay widget after product gallery
      // loaded. This is required for the elements which are rendered within
      // the gallery / product zoom container that refreshes on every variant
      // change or page load.
      $(document).once('product-gallery-loaded').on('productGalleryLoaded', function () {
        // Use the selected variant SKU on variant change.
        Drupal.hasValue(selectedVariant) ? setPostpayWidgetAmount(skuBaseForm, selectedVariant) : setPostpayWidgetAmount(skuBaseForm);
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
    } else if (productData.type != 'simple') {
      // Use the selected variant if available, else proceed.
      if (!Drupal.hasValue(variant)) {
        // Use first child provided in settings if available.
        // Use the first variant otherwise.
        var configurableCombinations = window.commerceBackend.getConfigurableCombinations(sku);
        variant = (typeof configurableCombinations.firstChild === 'undefined')
          ? Object.keys(variants)[0]
          : configurableCombinations.firstChild;

        // Check if we are in mag-v2 layout
        // due to different markup the initial variant fetch might fail.
        if (!Drupal.hasValue(variant)) {
          if ($('body').hasClass('magazine-layout-v2')) {
            // variantselected is an attribute in magv2 form.
            variant = $(element).attr('variantselected');
          }
        }
      }
    }
    // @todo Check this works for all kinds of products:
    // simple, simple grouped, configurable, configurable grouped and matchback.
    var variantPrice = (productData.type != 'simple' && variant) ?
      productData['variants'][variant]['gtm_price'] :
      productData.gtm_attributes.price;

    // No need to add a condition to check if the amount is changed, Postpay
    // takes care of that.
    $('.postpay-widget', product).attr('data-amount', (variantPrice.replace(',', '') * drupalSettings.postpay.currency_multiplier).toFixed(0));
    postpay.ui.refresh();
  }
})(jQuery, Drupal);
