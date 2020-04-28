(function ($, Drupal, document) {
  'use strict';

  Drupal.behaviors.alshayaSpcAddToCart = {
    attach: function (context, settings) {

      $('form.sku-base-form').on('submit.validate', function (event) {
        // Stop submitting form via normal process as this refreshes/redirects
        // the page on submit button click.
        return false;
      });

      $('.edit-add-to-cart', context).once('spc-add-to-cart').on('mousedown', function () {
        var that = this;
        setTimeout(function () {
          // If no ife error, we process further for add to cart.
          if (!$(that).closest('form').hasClass('ajax-submit-prevented')) {
            // Get closest `add to cart` form.
            var form = that.closest('form');
            var currentSelectedVariant = $(form).attr('data-sku');
            var page_main_sku = currentSelectedVariant;
            var variant_sku = '';
            // If sku is variant type.
            var is_configurable = $(form).attr('data-sku-type') === 'configurable';
            // If `selected_variant_sku` available, means its configurable.
            if ($('[name="selected_variant_sku"]', form).length > 0) {
              currentSelectedVariant = $('[name="selected_variant_sku"]', form).val();
              variant_sku = currentSelectedVariant;
            }

            var viewMode = $(form).closest('article[gtm-type="gtm-product-link"]').attr('data-vmode');
            var productKey = (viewMode === 'matchback') ? 'matchback' : 'productInfo';

            var quantity = 1;
            // If quantity drop down available, use that value.
            if ($('[name="quantity"]', form).length > 0) {
              quantity = $('[name="quantity"]', form).val();
            }

            var cart_action = 'add item';

            var cart_data = Drupal.alshayaSpc.getCartData();
            var cart_id = (cart_data) ? cart_data.cart_id : null;

            // We pass configurable options if product is not available in cart
            // and of configurable variant.
            var options = new Array();
            if (is_configurable) {
              currentSelectedVariant = $(form).find('.selected-parent-sku').val();
              Object.keys(settings.configurableCombinations[page_main_sku].configurables).forEach(function(key) {
                var option = {
                  'option_id': settings.configurableCombinations[page_main_sku].configurables[key].attribute_id,
                  'option_value': $(form).find('[data-configurable-code="' + key + '"]').val()
                };

                // Skipping the psudo attributes.
                if (settings.psudo_attribute === undefined || settings.psudo_attribute !== option.option_id) {
                  options.push(option);
                }
              });
            }

            // Prepare the POST data.
            var post_data = {
              'action': cart_action,
              'sku': currentSelectedVariant,
              'quantity': quantity,
              'cart_id': cart_id,
              'options': options,
            };

            var productData = {
              quantity: quantity,
              parentSku: page_main_sku,
              sku: currentSelectedVariant,
              variant: variant_sku,
              product_name: is_configurable ? settings[productKey][page_main_sku].variants[variant_sku].cart_title : settings.productInfo[page_main_sku].cart_title,
              image: is_configurable ? settings[productKey][page_main_sku].variants[variant_sku].cart_image : settings.productInfo[page_main_sku].cart_image,
            };

            // Post to ajax for cart update/create.
            jQuery.ajax({
              url: settings.alshaya_spc.cart_update_endpoint,
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              data: JSON.stringify(post_data),
              success: function (response) {
                // If there any error we throw from middleware.
                if (response.error === true) {
                  if (response.error_code === '400') {
                    Drupal.alshayaSpc.clearCartData();
                    $(that).trigger('click');
                    return;
                  }
                  var cleaned_sku = $(form).attr('data-cleaned-sku');
                  // Showing the error message.
                  $('.error-container-' + cleaned_sku).html('<div class="error">' + response.error_message + '</div>');
                  // Trigger the failed event for other listeners.
                  $(form).trigger('product-add-to-cart-failed', [productData, response]);
                }
                else if (response.cart_id) {
                  if (response.response_message.status === 'success'
                    && (typeof response.items[productData.variant] !== 'undefined'
                      || typeof response.items[productData.parentSku] !== 'undefined')) {
                    var cartItem = typeof response.items[productData.variant] !== 'undefined' ? response.items[productData.variant] : response.items[productData.parentSku];
                    productData.totalQty = cartItem.qty;
                  }

                  // Clean error message.
                  $('.error-container-' + cleaned_sku).html('');

                  // Trigger the success event for other listeners.
                  $(form).trigger('product-add-to-cart-success', [productData, response]);

                  var productInfo = drupalSettings.productInfo[productData.parentSku];
                  var options = [];
                  var productUrl = productInfo.url;
                  var price = productInfo.priceRaw;
                  var promotions = productInfo.promotions;
                  var productDataSKU = productData.sku;
                  var parentSKU = productData.sku;
                  var maxSaleQty = productData.maxSaleQty;
                  var maxSaleQtyParent = productData.max_sale_qty_parent;
                  var gtmAttributes = productData.gtm_attributes;

                  if (productInfo.type === 'configurable') {
                    var productVariantInfo = productInfo['variants'][productData.variant];
                    productDataSKU = productData.variant;
                    price = productVariantInfo.priceRaw;
                    parentSKU = productVariantInfo.parent_sku;
                    promotions = productVariantInfo.promotions;
                    options = productVariantInfo.configurableOptions;
                    maxSaleQty = productVariantInfo.maxSaleQty;
                    maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

                    if (productVariantInfo.url !== undefined) {
                      var langcode = $('html').attr('lang');
                      productUrl = productVariantInfo.url[langcode];
                    }
                  }
                  else if (productInfo.group !== undefined) {
                    var productVariantInfo = productInfo.group[productData.sku];
                    price = productVariantInfo.priceRaw;
                    parentSKU = productVariantInfo.parent_sku;
                    promotions = productVariantInfo.promotions;
                    maxSaleQty = productVariantInfo.maxSaleQty;
                    maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

                    var langcode = $('html').attr('lang');
                    productUrl = productVariantInfo.url[langcode];
                  }

                  Drupal.alshayaSpc.storeProductData(
                    productDataSKU,
                    parentSKU,
                    productData.product_name,
                    productUrl,
                    productData.image,
                    price,
                    options,
                    promotions,
                    maxSaleQty,
                    maxSaleQtyParent,
                    gtmAttributes
                  );

                  // Triggering event to notify react component.
                  var event = new CustomEvent('refreshMiniCart', {bubbles: true, detail: { data: () => response, productData }});
                  document.dispatchEvent(event);

                  var event = new CustomEvent('refreshCart', {bubbles: true, detail: { data: () => response }});
                  document.dispatchEvent(event);
                }
              }
            });
          }
        }, 20);
      });
    }
  };

})(jQuery, Drupal, document);
