(function ($, Drupal, document) {
  'use strict';

  Drupal.behaviors.alshayaSpcAddToCart = {
    attach: function (context, settings) {

      $('form.sku-base-form').on('submit.validate', function (event) {
        // Stop submitting form via normal process as this refreshes/redirects
        // the page on submit button click.
        return false;
      });

      $('.edit-add-to-cart', context).on('mousedown', function () {
        var that = this;
        setTimeout(function () {
          // If no ife error, we process further for add to cart.
          if (!$(that).closest('form').hasClass('ajax-submit-prevented')) {
            // Get closest `add to cart` form.
            var form = that.closest('form');
            var currentSelectedVariant = $(form).attr('data-sku');

            // If sku is variant type.
            var is_configurable = false;
            // If `selected_variant_sku` available, means its configurable.
            if ($('[name="selected_variant_sku"]', form).length > 0) {
              currentSelectedVariant = $('[name="selected_variant_sku"]', form).val();
              is_configurable = true;
            }

            var quantity = 1;
            // If quantity drop down available, use that value.
            if ($('[name="quantity"]', form).length > 0) {
              quantity = $('[name="quantity"]', form).val();
            }

            var cart_action = 'create cart';

            var cart_id = null;
            var options = new Array();
            var cart_data = localStorage.getItem('cart_data');
            var available_in_cart = false;
            if (cart_data) {
              cart_data = JSON.parse(cart_data);
              cart_id = cart_data.cart_id;
              cart_action = 'add item';

              // Adjust the quantity as available in cart already.
              if (cart_data.items.currentSelectedVariant !== undefined) {
                quantity = cart_data.items.currentSelectedVariant.qty + quantity;
                cart_action = 'update item';
                available_in_cart = true;
              }
            }

            // We pass configurable options if product is not available in cart
            // and of configurable variant.
            if (is_configurable && !available_in_cart) {
              var simple = currentSelectedVariant;
              var configurable = $(form).attr('data-sku');
              if (settings.configurableCombinations[configurable].bySku[simple]) {
                Object.keys(settings.configurableCombinations[configurable].bySku[simple]).forEach(function(key) {
                  var option = {
                    'option_id': settings.configurableCombinations[configurable].configurables[key].attribute_id,
                    'option_value': settings.configurableCombinations[configurable].bySku[simple][key]
                  };
                  options.push(option);
                  currentSelectedVariant = configurable;
                });
              }
            }

            // Prepare the POST data.
            var post_data = {
              'action': cart_action,
              'sku': currentSelectedVariant,
              'quantity': quantity,
              'cart_id': cart_id,
              'options': options
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
                  var cleaned_sku = $(form).attr('data-cleaned-sku');
                  // Showing the error message.
                  $('.error-container-' + cleaned_sku).html('<div class="error">' + response.error_message + '</div>');
                  // Trigger the failed event for other listeners.
                  $(form).trigger('product-add-to-cart-failed');
                }
                else if (response.cart_id) {
                  // Clean error message.
                  $('.error-container-' + cleaned_sku).html('');

                  // Trigger the success event for other listeners.
                  $(form).trigger('product-add-to-cart-success');

                  // Triggering event to notify react component.
                  var event = new CustomEvent('refreshMiniCart', {bubbles: true, detail: { data: () => response }});
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
