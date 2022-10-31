(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.alshayaRcs = Drupal.alshayaRcs || {};

  Drupal.behaviors.alshayaRcsModalBehavior = {
    attach: function (context, settings) {
      $('.open-modal-pdp, #dy-recommendation a.product-quick-view-link').once('pdp-modal-processed').on('click', function (e) {
        e.preventDefault();

        // Display loader.
        if (typeof Drupal.cartNotification.spinner_start === 'function') {
          document.querySelector('body').scrollIntoView({
            behavior: 'smooth',
          });
          Drupal.cartNotification.spinner_start();
        }

        // Try to get sku from the element clicked. Works with DY block.
        var sku = $(this).data('sku');
        if (!sku) {
          // Try to get sku from parent article. Works with RCS recommended block.
          sku = $(this).parent('article').data('sku');
        }

        globalThis.rcsPhCommerceBackend.getData('product-recommendation', {sku: sku}, null, null, null, true)
          .then(async function (entity) {
            if (entity === null || typeof entity === 'undefined') {
              return;
            }

            var currencyConfig = drupalSettings.alshaya_spc.currency_config;
            var data = {
              is_page: false,
              title_prefix: '',
              entity: entity,
              language: drupalSettings.path.currentLanguage,
              // @todo Create a function as this is also done in alshaya_rcs_magazine.js
              price_details: {
                display_mode: drupalSettings.alshayaRcs.priceDisplayMode,
                discount: {
                  percent_off: Math.round(entity.price_range.maximum_price.discount.percent_off)
                },
                regular_price: {
                  value: entity.price_range.maximum_price.regular_price.value,
                  currency_code: currencyConfig.currency_code,
                  currency_code_position: currencyConfig.currency_code_position,
                  decimal_points: currencyConfig.decimal_points,
                },
                final_price: {
                  value: entity.price_range.maximum_price.final_price.value,
                  currency_code: currencyConfig.currency_code,
                  currency_code_position: currencyConfig.currency_code_position,
                  decimal_points: currencyConfig.decimal_points,
                },
              },
              sku_out_of_stock: false, //@todo Review this variable: It is used in few twig templates but never populated
              size_volume: Drupal.hasValue(entity.size_volume) ? entity.size_volume : '',
              vat_text: drupalSettings.vat_text,
              quantity_limit_enabled: drupalSettings.alshayaRcs.quantity_limit_enabled,
              image_slider_position_pdp: drupalSettings.alshaya_white_label.image_slider_position_pdp,
              promotions: globalThis.rcsPhRenderingEngine.computePhFilters(entity, 'promotions'),
              postpay: Drupal.hasValue(drupalSettings.postpay_widget_info) ? drupalSettings.postpay_widget_info : {},
              tabby: Drupal.hasValue(drupalSettings.tabby) ? drupalSettings.tabby.widgetInfo : {},
              cleanSku: Drupal.cleanCssIdentifier(entity.sku),
              is_wishlist_enabled: drupalSettings.alshayaRcs.isWishlistEnabled,
            };

            var elem = document.createElement('div');
            elem.innerHTML = handlebarsRenderer.render('product.modal', data);

            // Open modal.
            Drupal.dialog(elem, {
              dialogClass: 'pdp-modal-box',
              autoResize: false,
              closeOnEscape: false,
              width: 'auto',
              title: entity.name,
            }).showModal();

            $('.pdp-modal-box').find('.ui-widget-content').attr('id', 'drupal-modal');

            // Modal quantity field.
            $('.sku-base-form .form-item-quantity .form-select').once('select2select').select2({
              minimumResultsForSearch: -1
            });

            // Call behaviours with modal context.
            var modalContext = document.querySelector('.pdp-modal-box');
            globalThis.rcsPhApplyDrupalJs(modalContext);

            var mainProduct = entity;
            // Now render the add to cart form.
            if (Drupal.hasValue(window.commerceBackend.getProductsInStyle)) {
              mainProduct = await window.commerceBackend.getProductsInStyle(mainProduct);
            }
            window.commerceBackend.renderAddToCartForm(mainProduct);
            globalThis.rcsPhApplyDrupalJs(modalContext);
          },
          function () {
            // @todo shall we remove loaders when this happens?
            Drupal.alshayaLogger('error', 'Could not fetch data for product recommendation!');
          });

        return false;
      });

      // Modal view for the free gift.
      $('.free-gift-message a').once().on('click', async function(e) {
        e.preventDefault();

        // Try to get sku from the element clicked.
        var skus = $(this).data('sku').split(',');
        if (skus.length === 1) {
          // Load the product data based on sku.
          var freeGiftProduct = globalThis.RcsPhStaticStorage.get('product_data_' + skus[0]);
          if (freeGiftProduct) {
            var data = {
              entity: freeGiftProduct,
              language: drupalSettings.path.currentLanguage,
              is_page: false,
              item_code: skus[0],
              image_slider_position_pdp: drupalSettings.alshaya_white_label.image_slider_position_pdp,
            };
            var elem = document.createElement('div');
            elem.innerHTML = handlebarsRenderer.render('product.promotion_free_gift_modal', data);

            // Open modal.
            Drupal.dialog(elem, {
              dialogClass: 'pdp-modal-box',
              autoResize: false,
              closeOnEscape: true,
              width: 'auto',
            }).showModal();

            $('.pdp-modal-box').find('.ui-widget-content').attr('id', 'drupal-modal');

            // Call behaviours with modal context.
            var modalContext = $('.pdp-modal-box');
            globalThis.rcsPhApplyDrupalJs(modalContext);
          }
        } else if (skus.length > 1) {
          // @todo Handle the scenario of multiple free gift.
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
