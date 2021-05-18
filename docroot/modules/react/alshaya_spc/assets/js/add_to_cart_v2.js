(function ($, Drupal, document) {
  'use strict';

  Drupal.behaviors.alshayaSpcAddToCart = {
    attach: function (context, settings) {

      $('form.sku-base-form').on('submit.validate', function (event) {
        // Stop submitting form via normal process as this refreshes/redirects
        // the page on submit button click.
        return false;
      });

      // Clear form errors.
      let clearFormErrors = function (form) {
        var cleanedSku = $(form).attr('data-cleaned-sku');
        $('.error-container-' + cleanedSku).html('');
      };

      $('.edit-add-to-cart', context).once('spc-add-to-cart').on('mousedown', function () {
        // Check for ife error.
        if ($(this).closest('form').hasClass('ajax-submit-prevented')) {
          return;
        }

        var that = this;
        var form = $(that).closest('form');
        // Get cart ID, add item and update cart.
        $.when(Drupal.alshayaSpc.getCartId())
          .then(function (cartId) {
            var product = getProduct(form);
            var quantity = getQuantity(form);

            // Add item and update cart.
            $.when(addItemToCart(form, cartId, product, quantity))
              .then(function (response) {
                //@todo test this
                if (response.error === true) {
                  if (response.error_code === '400') {
                    Drupal.alshayaSpc.clearCartData();
                    $(that).trigger('click');
                    return;
                  }

                  // Showing the error message.
                  var closestForm = $(that).closest('form.sku-base-form');
                  let errorMessage = response.error_message;
                  if (response.error_code === '604') {
                    errorMessage = Drupal.t('The product that you are trying to add is not available.');
                  }
                  $(closestForm).find('.errors-container').html('<div class="error">' + errorMessage + '</div>');

                  // Process required data and trigger add to cart failure event.
                  productData.options = [];

                  // Get the key-value pair of selected option name and value.
                  $('#configurable_ajax select').each(function () {
                    var configLabel = $(this).attr('data-default-title');
                    var configValue = $(this).find('option:selected').text();
                    productData.options.push(configLabel + ': ' + configValue);
                  });

                  // Prepare the event.
                  var cartNotification = new CustomEvent('product-add-to-cart-failed', {
                    bubbles: true,
                    detail: {
                      postData: postData,
                      productData: productData,
                      message: response.error_message,
                    },
                  });
                  // Dispatch event so that handlers can process it.
                  // @todo this is not doing anything because alshaya_track_js module is disabled
                  form[0].dispatchEvent(cartNotification);
                  return;
                }

                // Clean error messages.
                clearFormErrors(form);

                // Update cart.
                var cartData = Drupal.alshayaSpc.fetchCartData();
                sendNotitifications(cartData);

                // Update local storage.
                updateLocalStorage(product);
              })
              .fail(function () {
                showFormErrorMessage(form, product, Drupal.t('Error adding item to the cart.'));
                // This error is typically caused by invalid cartId. Force a new cart to be created.
                Drupal.alshayaSpc.clearCartData();
              });
          })
          .fail(function () {
            showFormErrorMessage(form, cartId, Drupal.t('Error creating a cart.'));
          });
      });

      // Add product to cart.
      let addItemToCart = function (form, cartId, product, quantity) {
        var postData = {
          "cartItem": {
            "quote_id": cartId,
            "sku": product.variant,
            "qty": quantity,
          },
        };

        // @todo remove proxy.
        return $.ajax({
          async: false,
          url: '/proxy.php?url=' + encodeURI(settings.cart.url + '/' + settings.cart.store + '/rest/V1/guest-carts/' + cartId + '/items'),
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          data: JSON.stringify(postData),
          error: function () {
            return false;
          },
          success: function (response) {
            // Trigger the success event for other listeners.
            var event = $.Event('product-add-to-cart-success', {
              detail: {
                postData: postData,
                productData: product,
                cartData: Drupal.alshayaSpc.getCartData(),
              },
            });
            $(form).trigger(event);
            return response;
          },
        });

        // return response;
      };

      // Update local storage.
      let updateLocalStorage = function (productData) {
        var productInfo = productData.productInfo;
        var langCode = $('html').attr('lang');

        // @todo uncomment and fix this
        // if (response.response_message === null || response.response_message.status === 'success') {
        //   if (typeof response.items[productData.variant] !== 'undefined') {
        //     productData.totalQty = response.items[productData.variant];
        //   } else if (typeof response.items[productData.parentSku] !== 'undefined') {
        //     productData.totalQty = response.items[productData.parentSku];
        //   }
        // }

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
        var options = [];

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
            productUrl = productVariantInfo.url[langCode];
          }
          gtmAttributes.price = productVariantInfo.gtm_price || price;
        } else if (productInfo.group !== undefined) {
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

          productUrl = productVariantInfo.url[langCode];
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
      };

      // Get product.
      let getProduct = function (form) {
        var viewMode = $(form).closest('article[gtm-type="gtm-product-link"]').attr('data-vmode');

        // Decide the key from which we load product data.
        // It will be in productInfo for all cases except matchback.
        var key = (viewMode === 'matchback' || viewMode === 'matchback_mobile')
          ? viewMode
          : 'productInfo';

        var isConfigurable = $(form).attr('data-sku-type') === 'configurable';
        var currentSelectedVariant = $(form).attr('data-sku');
        var pageMainSku = currentSelectedVariant;
        var productInfo = settings[key][pageMainSku];
        var productName = productInfo.cart_title;
        var productImage = productInfo.cart_image;
        var variantSku = '';
        var options = [];

        // If `selected_variant_sku` available, means its configurable.
        if ($('[name="selected_variant_sku"]', form).length > 0) {
          currentSelectedVariant = $('[name="selected_variant_sku"]', form).val();
          variantSku = currentSelectedVariant;
        }

        // Configurable - normal as well as re-structured.
        if (isConfigurable) {
          productName = productInfo.variants[variantSku].cart_title;
          productImage = productInfo.variants[variantSku].cart_image;
          currentSelectedVariant = $(form).find('.selected-parent-sku').val();

          // Add options.
          Object.keys(settings.configurableCombinations[pageMainSku].configurables).forEach(function (key) {
            var optionId = settings.configurableCombinations[pageMainSku].configurables[key].attribute_id;
            // Skipping the psudo attributes.
            if (settings.psudo_attribute === undefined || settings.psudo_attribute !== optionId) {
              options.push(
                {
                  option_id: settings.configurableCombinations[pageMainSku].configurables[key].attribute_id,
                  option_value: $(form).find('[data-configurable-code="' + key + '"]').val()
                }
              );
            }
          });
        }

        // Simple grouped (re-structured).
        else if (productInfo['group'] !== undefined) {
          productName = productInfo['group'][currentSelectedVariant].cart_title;
          productImage = productInfo['group'][currentSelectedVariant].cart_image;
        }

        // Prepare product data.
        var product = {
          //quantity: quantity, //@todo add this somewhere
          parentSku: pageMainSku,
          sku: currentSelectedVariant,
          variant: variantSku,
          product_name: productName,
          image: productImage,
          productInfo: productInfo,
        };

        return product;
      };

      // Get quantity.
      let getQuantity = function (form) {
        var quantity = 1;

        if ($('[name="quantity"]', form).length > 0) {
          quantity = $('[name="quantity"]', form).val();
        }

        return quantity;
      };

      // Show message and track error.
      let showFormErrorMessage = function (form, data, message) {
        $(form).find('.errors-container').html('<div class="error">' + message + '</div>');

        // Send notification
        var event = new CustomEvent('product-add-to-cart-error', {
          bubbles: true,
          detail: {
            postData: data,
            message: message,
          },
        });
        // @todo check why alshaya_track_js module is disabled.
        form[0].dispatchEvent(event);
      };

      // Send event notifications.
      let sendNotitifications = function (cartData) {
        var event = null;
        // Triggering event to notify react component.
        // @todo find a way to pass productData
        event = new CustomEvent('refreshMiniCart', {
          bubbles: true,
          detail: {
            data: () => cartData,
            //productData: ???,
          },
        });
        document.dispatchEvent(event);

        event = new CustomEvent('refreshCart', {
          bubbles: true,
          detail: {
            data: () => cartData,
          },
        });
        document.dispatchEvent(event);

        // We want to refresh Recommended product on add to cart
        // functionality but only on cart page.
        //@todo test this
        if ($('#spc-cart').length > 0) {
          event = new CustomEvent('spcRefreshCartRecommendation', {
            bubbles: true,
            detail: {
              items: cartData.items,
            },
          });
          document.dispatchEvent(event);
        }
      };
    },
  };
})(jQuery, Drupal, document);
