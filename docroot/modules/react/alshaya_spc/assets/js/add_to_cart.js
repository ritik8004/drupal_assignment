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
            var form = $(that).closest('form');
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
            // Decide the key from which we load product data.
            // It will be in productInfo for all cases except matchback.
            var productInfoKey = (viewMode === 'matchback' || viewMode === 'matchback_mobile')
              ? viewMode
              : 'productInfo';

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
              // This will be useful on add to cart errors.
              'variant_sku': variant_sku,
            };

            var productData = {
              quantity: quantity,
              parentSku: page_main_sku,
              sku: currentSelectedVariant,
              variant: variant_sku,
            };

            productData['product_name'] = settings[productInfoKey][page_main_sku].cart_title;
            productData['image'] = settings[productInfoKey][page_main_sku].cart_image;

            // Configurable - normal as well as re-structured.
            if (is_configurable) {
              productData['product_name'] = settings[productInfoKey][page_main_sku].variants[variant_sku].cart_title;
              productData['image'] = settings[productInfoKey][page_main_sku].variants[variant_sku].cart_image;
            }
            // Simple grouped (re-structured).
            else if (settings[productInfoKey][page_main_sku]['group'] !== undefined) {
              productData['product_name'] = settings[productInfoKey][page_main_sku]['group'][currentSelectedVariant].cart_title;
              productData['image'] = settings[productInfoKey][page_main_sku]['group'][currentSelectedVariant].cart_image;
            }

            // Post to ajax for cart update/create.
            window.commerceBackend.addUpdateRemoveCartItem(post_data)
              .then (function (responseData) {
                const response = responseData.data;
                // If there any error we throw from middleware.
                if (response.error === true) {
                  if (response.error_code === '400') {
                    Drupal.alshayaSpc.clearCartData();
                    $(that).trigger('click');
                    return;
                  }
                  var closestForm = $(that).closest('form.sku-base-form');
                  let errorMessage = response.error_message;
                  if (response.error_code === '604') {
                    errorMessage = Drupal.t('The product that you are trying to add is not available.');
                  }

                  // Showing the error message.
                  $(closestForm).find('.errors-container').html('<div class="error">' + errorMessage + '</div>');

                  // Process required data and trigger add to cart failure event.
                  productData.options = [];

                  // Get the key-value pair of selected option name and value.
                  $('#configurable_ajax select').each(function () {
                    var configLabel = $(this).attr('data-default-title');
                    var configValue = $(this).find('option:selected').text();
                    productData.options.push(configLabel + ": " + configValue);
                  });

                  // Prepare the event.
                  var cartNotification = new CustomEvent('product-add-to-cart-failed', {
                    bubbles: true,
                    detail: {
                      postData: post_data,
                      productData: productData,
                      message: response.error_message,
                    },
                  });
                  // Dispatch event so that handlers can process it.
                  form[0].dispatchEvent(cartNotification);
                }
                else if (response.cart_id) {
                  if ((response.response_message === null || response.response_message.status === 'success')
                    && (typeof response.items[productData.variant] !== 'undefined' || typeof response.items[productData.parentSku] !== 'undefined')) {
                    var cartItem = typeof response.items[productData.variant] !== 'undefined' ? response.items[productData.variant] : response.items[productData.parentSku];
                    productData.totalQty = cartItem.qty;
                  }

                  // Clean error message.
                  var cleaned_sku = $(form).attr('data-cleaned-sku');
                  $('.error-container-' + cleaned_sku).html('');

                  // Trigger the success event for other listeners.
                  var cartNotification = jQuery.Event('product-add-to-cart-success', {
                    detail: {
                      postData: post_data,
                      productData: productData,
                      cartData: response,
                    }
                  });
                  $(form).trigger(cartNotification);

                  var productInfo = drupalSettings[productInfoKey][productData.parentSku];
                  var options = [];
                  var productUrl = productInfo.url;
                  var price = productInfo.priceRaw;
                  var promotions = productInfo.promotionsRaw;
                  var freeGiftPromotion = productInfo.freeGiftPromotion;
                  var productDataSKU = productData.sku;
                  var parentSKU = productData.sku;
                  var maxSaleQty = productInfo.maxSaleQty;
                  var maxSaleQtyParent = productInfo.max_sale_qty_parent;
                  var gtmAttributes = productInfo.gtm_attributes;
                  var isNonRefundable = productInfo.is_non_refundable;

                  if (productInfo.type === 'configurable') {
                    var productVariantInfo = productInfo['variants'][productData.variant];
                    productDataSKU = productData.variant;
                    price = productVariantInfo.priceRaw;
                    parentSKU = productVariantInfo.parent_sku;
                    promotions = productVariantInfo.promotionsRaw;
                    freeGiftPromotion = productVariantInfo.freeGiftPromotion || freeGiftPromotion;
                    options = productVariantInfo.configurableOptions;
                    maxSaleQty = productVariantInfo.maxSaleQty;
                    maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

                    if (productVariantInfo.url !== undefined) {
                      var langcode = $('html').attr('lang');
                      productUrl = productVariantInfo.url[langcode];
                    }
                    gtmAttributes.price = productVariantInfo.gtm_price || price;
                  }
                  else if (productInfo.group !== undefined) {
                    var productVariantInfo = productInfo.group[productData.sku];
                    price = productVariantInfo.priceRaw;
                    parentSKU = productVariantInfo.parent_sku;
                    promotions = productVariantInfo.promotionsRaw;
                    freeGiftPromotion = productVariantInfo.freeGiftPromotion || freeGiftPromotion;
                    if (productVariantInfo.grouping_options !== undefined
                      && productVariantInfo.grouping_options.length > 0) {
                      options = productVariantInfo.grouping_options;
                    }
                    maxSaleQty = productVariantInfo.maxSaleQty;
                    maxSaleQtyParent = productVariantInfo.max_sale_qty_parent;

                    var langcode = $('html').attr('lang');
                    productUrl = productVariantInfo.url[langcode];
                    gtmAttributes.price = productVariantInfo.gtm_price || price;
                  }

                  // Store proper variant sku in gtm data now.
                  gtmAttributes.variant = productDataSKU;
                  Drupal.alshayaSpc.storeProductData({
                    sku: productDataSKU,
                    parentSKU: parentSKU,
                    title: productData.product_name,
                    url: productUrl,
                    image: productData.image,
                    price: price,
                    options: options,
                    promotions: promotions,
                    freeGiftPromotion: freeGiftPromotion,
                    maxSaleQty: maxSaleQty,
                    maxSaleQtyParent: maxSaleQtyParent,
                    gtmAttributes: gtmAttributes,
                    isNonRefundable: isNonRefundable,
                  });

                  // Triggering event to notify react component.
                  var event = new CustomEvent('refreshMiniCart', {
                    bubbles: true,
                    detail: {
                      data: function () {
                        return response;
                      },
                      productData: productData,
                    }
                  });
                  document.dispatchEvent(event);

                  var event = new CustomEvent('refreshCart', {bubbles: true, detail: { data: (function () { return response; })}});
                  document.dispatchEvent(event);

                  // We want to refresh Recommended product on add to cart
                  // functionality but only on cart page.
                  if ($('#spc-cart').length > 0) {
                    document.dispatchEvent(
                      new CustomEvent(
                        'spcRefreshCartRecommendation',
                        {
                          bubbles: true,
                          detail: {  items: response.items  }
                        }
                      )
                    );
                  }
                }
              })
              .catch (function(error) {
                // Send notification.
                var cartNotification = new CustomEvent('product-add-to-cart-error', {
                  bubbles: true,
                  detail: {
                    postData: post_data,
                  },
                });
                form[0].dispatchEvent(cartNotification);
              });
          }
        }, 20);
      });
    }
  };

})(jQuery, Drupal, document);
