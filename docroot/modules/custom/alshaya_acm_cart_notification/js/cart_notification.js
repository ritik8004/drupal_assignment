(function ($, Drupal, document) {
  'use strict';

  Drupal.behaviors.alshayaAcmCartNotification = {
    attach: function (context, settings) {
      $(window).on('click', function() {
        // check if element is Visible
        var length = $('#cart_notification').html().length;
        if (length > 0) {
          $('#cart_notification').empty();
          $('body').removeClass('notification--on');
          $('#cart_notification').removeClass('has--notification')
        }
      });

      // Stop event from inside container to propogate out.
      $('#cart_notification').once('bind-events').on('click', function (event) {
        event.stopPropagation();
      });

      // Create a new instance of ladda for the specified button
      $('.edit-add-to-cart').attr('data-style', 'zoom-in');
      var l = $('.edit-add-to-cart').ladda();

      $('.edit-add-to-cart', context).on('click', function () {
        // Start loading
        $(this).ladda('start');
      });

      $('.edit-add-to-cart', context).on('mousedown', function () {
        // Start loading
        $(this).ladda('start');
      });

      $('.edit-add-to-cart', context).on('keydown', function (event) {
        if (event.keyCode === 13 || event.keyCode === 32) {
          // Start loading
          $(this).ladda('start');
        }
      });

      $('[data-drupal-selector="edit-configurables-size"]').on('change', function () {
        // Start loading.
        $(this).closest('.sku-base-form').find('.edit-add-to-cart').ladda('start');
      });

      $(document).ajaxComplete(function (event, xhr, settings) {
        if ((settings.hasOwnProperty('extraData')) && (settings.extraData._triggering_element_name === 'configurables[size]')) {
          $(this).stopSpinner(['success']);
        }
        else if (!settings.hasOwnProperty('extraData')) {
          $.ladda('stopAll');
        }
      });

      $.fn.cartNotificationScroll = function () {
        $('html,body').animate({
          scrollTop: $('.header--wrapper').offset().top
        }, 'slow');
          $('body').addClass('notification--on');
        $('#cart_notification').addClass('has--notification');

        setTimeout(function () {
          $('#cart_notification').fadeOut();
        }, 10000);
      };

      $.fn.cartGenericScroll = function (selector) {
        if ($(window).width() < 768 && $('body').find(selector).length !== 0) {
          $('html,body').animate({
            scrollTop: $(selector).offset().top - $('.branding__menu').height() - 100
          }, 'slow');
        }
      };

      $.fn.stopSpinner = function (data) {
        l.ladda('stop');
        if (data.message === 'success') {
          $('.edit-add-to-cart', context).find('.ladda-label').html(Drupal.t('added'));
          var pdpAddCartButton = l;
          var addedProduct = pdpAddCartButton.closest('article[gtm-type="gtm-product-link"]');
          var quantity = parseInt(pdpAddCartButton.closest('.sku-base-form').find('.form-item-quantity select').val());
          var size = pdpAddCartButton.closest('.sku-base-form').find('.form-item-configurables-size select option:selected').text();
          var selectedVariant = '';

          if (addedProduct.attr('gtm-sku-type') === 'configurable') {
            selectedVariant = addedProduct.find('.selected-variant-sku-' + addedProduct.attr('gtm-product-sku').toLowerCase()).val();
          }

          if ($('.ui-dialog').length > 0) {
            // Expose details of product being added to the cart before closing the modal.
            var productModalSelector = $('.ui-dialog');
            addedProduct = productModalSelector.find('article[gtm-type="gtm-product-link"]');
            quantity = parseInt(productModalSelector.find('.form-item-quantity select').val());
            size = productModalSelector.find('.form-item-configurables-size select option:selected').text();

            $('.ui-dialog .ui-dialog-titlebar-close').trigger('click');
          }

          if (addedProduct.length > 0) {
            var product = Drupal.alshaya_seo_gtm_get_product_values(addedProduct);
            // Remove product position: Not needed while adding to cart.
            delete product.position;

            // Set product quantity to selected quatity.
            product.quantity = quantity;

            // Set product size to selected size.
            if (product.dimension5 !== 'simple') {
              var currentLangCode = drupalSettings.path.currentLanguage;
              if (currentLangCode !== 'en') {
                size = drupalSettings.alshaya_product_size_config[size];
              }
              product.dimension1 = size;
            }

            // Set product variant to the selected variant.
            if (product.dimension5 !== 'simple') {
              product.variant = selectedVariant;
            }
            else {
              product.variant = product.id;
            }

            // Calculate metric 1 value.
            product.metric1 = product.price * product.quantity;

            var productData = {
              'event': 'addToCart',
              'ecommerce': {
                'currencyCode': 'KWD',
                'add': {
                  'products': [
                    product
                  ]
                }
              }
            };

            dataLayer.push(productData);
          }
        }
        else if (data.message === 'failure') {
          $('.edit-add-to-cart', context).find('.ladda-label').html(Drupal.t('error'));
        }
        setTimeout(
          function () {
            $('.edit-add-to-cart').find('.ladda-label').html(Drupal.t('add to cart'));
          }, data.interval);
      };
    }
  };

})(jQuery, Drupal, document);
