/**
 * @file
 * RCS Free gift js file.
 */

(function ($, Drupal, drupalSettings) {
  // Variables to have collection and collection item dialog globally, so that
  // we can control the modal from all the functions.
  var collectionDialog = '';
  var collectionItemDialog = '';

  Drupal.behaviors.rcsFreeGifts = {
    attach: function (context, settings) {
      // On dialog close remove the free gift overlay related classes.
      $('.free-gifts-modal-overlay #free-gift-drupal-modal').once().on('dialogclose', function () {
        if ($('body').hasClass('free-gift-promo-list-overlay')) {
          $('body').removeClass('free-gift-promo-list-overlay');
        }
        $('body').removeClass('free-gifts-modal-overlay');
      });

      // We have two type of modals for free gift.
      // 1. The single item modal ( Where we display a individual free gift )
      // 2. Collection of item modal ( List of free gift items )

      // Modal view for the free gift.
      $('.free-gift-promotions .free-gift-wrapper .free-gift-message a, a.free-gift-modal').once('free-gift-processed').on('click', function (e) {
        e.preventDefault();

        // Close any other modal that is open.
        if (collectionDialog && collectionDialog.open) {
          collectionDialog.close();
        }

        // Display loader.
        if (typeof Drupal.cartNotification.spinner_start === 'function') {
          document.querySelector('body').scrollIntoView({
            behavior: 'smooth',
          });
          Drupal.cartNotification.spinner_start();
        }

        // Try to get sku from the element clicked.
        var skus = $(this).data('sku').split(',');
        var backToCollection = $(this).data('back-to-collection');
        if (skus.length === 1) {
          // Load the product data based on sku.
          var freeGiftProduct = globalThis.RcsPhStaticStorage.get('product_data_' + skus[0]);
          showItemModalView(freeGiftProduct, skus[0], backToCollection);
        } else if (skus.length > 1) {
          // Get the free gift promotion title.
          var promotionTitle = $(this).data('promotion-title');
          const freeGiftProducts = globalThis.rcsPhCommerceBackend.getDataSynchronous('multiple_products_by_sku', {
            sku: skus,
          });

          // Store the response in static storage.
          skus.forEach((freeGiftSku) => {
            // Traverse through all the products and validate the freeGiftSku
            // with parent and child sku.
            freeGiftProducts.forEach((freeGiftProduct) => {
              if (freeGiftProduct.sku === freeGiftSku) {
                globalThis.RcsPhStaticStorage.set('product_data_' + freeGiftSku, freeGiftProduct);
              } else {
                freeGiftProduct.variants.forEach((freeGiftVariant) => {
                  if (freeGiftVariant.product.sku === freeGiftSku) {
                    globalThis.RcsPhStaticStorage.set('product_data_' + freeGiftSku, freeGiftProduct);
                  }
                });
              }
            });
          });
          var elm = document.createElement('div');
          var data = {
            title: promotionTitle,
            items: [],
          }
          skus.forEach((sku) => {
            var freeGiftProduct = globalThis.RcsPhStaticStorage.get('product_data_' + sku);
            if (freeGiftProduct) {
              var freeGiftImage = window.commerceBackend.getFirstImage(freeGiftProduct);
              data.items.push({
                title: freeGiftProduct.name,
                freeGiftImage: Drupal.hasValue(freeGiftImage.url) ? freeGiftImage.url : '',
                freeGiftSku: sku,
                backToCollection: true,
              });
            }
          });

          elm.innerHTML = handlebarsRenderer.render('product.promotion_free_gift_items', data);
          // Remove the loader from the screen.
          Drupal.cartNotification.spinner_stop();

          // Open modal.
          collectionDialog = Drupal.dialog(elm, {
            dialogClass: 'pdp-modal-box',
            autoResize: false,
            closeOnEscape: true,
            width: 'auto',
            close: function close() {
              collectionDialog = '';
            },
          });
          collectionDialog.show();

          $('.pdp-modal-box').find('.ui-widget-content').attr('id', 'drupal-modal');
          // Call behaviours with modal context.
          var modalContext = $('.pdp-modal-box');
          globalThis.rcsPhApplyDrupalJs(modalContext);
        }
      });

      // Open the collection list on click of back to collection.
      $(".free-gift-back-to-collection").once('back-to-collection-processed').on('click', function(e) {
        e.preventDefault();
        // Close the item dialog box.
        if (collectionItemDialog) {
          collectionItemDialog.close();
        }
        $(".free-gift-message a").click();
      });
    }
  }

  /**
   * Utility function to show free gift item in dialog box.
   *
   * @param {object} freeGiftProduct
   *   The free gift product object.
   * @param {string} sku
   *   The product sku.
   * @param {boolean} backToCollection
   *   Boolean flag to show the back to collection link.
   */
  function showItemModalView(freeGiftProduct, sku, backToCollection = false) {
    var elm = document.createElement('div');
    // Remove the loader from the screen.
    Drupal.cartNotification.spinner_stop();

    if (freeGiftProduct) {
      var data = {
        entity: freeGiftProduct,
        language: drupalSettings.path.currentLanguage,
        is_page: false,
        item_code: sku,
        back_to_collection: backToCollection,
        image_slider_position_pdp: drupalSettings.alshaya_white_label.image_slider_position_pdp,
      };
      elm.innerHTML = handlebarsRenderer.render('product.promotion_free_gift_modal', data);
      // Show the modal.
      collectionItemDialog = Drupal.dialog(elm, {
        dialogClass: 'pdp-modal-box',
        autoResize: false,
        closeOnEscape: true,
        width: 'auto',
        close: function close() {
          collectionItemDialog = '';
        },
      });
      collectionItemDialog.show();

      // Only if the sku and the parent sku is same.
      if (sku === freeGiftProduct.sku) {
        window.commerceBackend.renderAddToCartForm(freeGiftProduct);
        // As we don't need the add to cart button, so removing it.
        $('.pdp-modal-box button.add-to-cart-button').remove();
      } else {
        // For simple products, just remove the add to cart skeletal.
        $('.pdp-modal-box .add_to_cart_form').addClass('rcs-loaded');
      }

      $('.pdp-modal-box').find('.ui-widget-content').attr('id', 'drupal-modal');
      // Call behaviours with modal context.
      var modalContext = $('.pdp-modal-box');
      globalThis.rcsPhApplyDrupalJs(modalContext);
    }
  };

})(jQuery, Drupal, drupalSettings);
