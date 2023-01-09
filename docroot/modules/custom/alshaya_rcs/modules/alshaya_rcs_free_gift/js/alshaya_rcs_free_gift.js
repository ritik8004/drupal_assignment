/**
 * @file
 * RCS Free gift js file.
 */

(function ($, Drupal, drupalSettings, RcsEventManager) {
  // Variables to have collection and collection item dialog globally, so that
  // we can control the modal from all the functions.
  var collectionDialog = '';
  var collectionItemDialog = '';
  window.commerceBackend = window.commerceBackend || {};

  /**
 * Renders free gift for the given product.
 *
 * @param {object} product
 *   The main product object.
 */
  function renderFreeGift(product) {
    var freeGiftWrapper = jQuery('.free-gift-promotions');
    var freeGiftHtml = globalThis.rcsPhRenderingEngine.computePhFilters(product, 'promotion_free_gift');
    // Render the HTML in the free gift wrapper.
    freeGiftWrapper.html(freeGiftHtml);
    freeGiftWrapper.addClass('rcs-loaded');
    globalThis.rcsPhApplyDrupalJs(document);
  }

  /**
   * Event listener of page entity to get the free gift product info.
   */
  RcsEventManager.addListener('alshayaPageEntityLoaded', async function pageEntityLoaded(e) {
    var mainProduct = e.detail.entity;
    // Get the list of all the available Free gifts.
    var freeGiftPromotion = mainProduct.free_gift_promotion;
    if (freeGiftPromotion.length > 0
      // We support displaying only one free gift promotion for now.
      && freeGiftPromotion[0].total_items > 0) {
      // Get the first free gift product info. In case there are multiple free
      // gift items, then we will load the product info during modal view.
      var giftItemSku = freeGiftPromotion[0].gifts[0].sku;
      var freeGiftProduct = await globalThis.rcsPhCommerceBackend.getData('product_by_sku', { sku: giftItemSku });

      if (Drupal.hasValue(freeGiftProduct) && Drupal.hasValue(freeGiftProduct.sku)) {
        window.commerceBackend.setRcsProductToStorage(freeGiftProduct, 'free_gift', giftItemSku);

        // Render the free gift item.
        renderFreeGift(mainProduct);
      }
    }
  });

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
      $('.free-gift-promotions .free-gift-wrapper .free-gift-message a, a.free-gift-modal').once('free-gift-processed').on('click', async function (e) {
        e.preventDefault();

        // Close any other modal that is open.
        if (collectionDialog && collectionDialog.open) {
          collectionDialog.close();
          $('body').addClass('free-gifts-modal-overlay');
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
        if (skus.length === 1) {
          var backToCollection = $(this).data('back-to-collection');
          // Load the product data based on sku.
          var freeGiftProduct = globalThis.RcsPhStaticStorage.get('product_data_' + skus[0]);
          showItemModalView(freeGiftProduct, skus[0], backToCollection);
          // Remove the loader from the screen.
          Drupal.cartNotification.spinner_stop();
        } else if (skus.length > 1) {
          // Get the free gift promotion title.
          var promotionTitle = $(this).data('promotion-title');
          var styleCode = $(this).data('style-code');

          var freeGiftProduct = await window.commerceBackend.getProductsInStyle({ sku: skus[0], style_code: styleCode });

          var elm = document.createElement('div');
          var data = {
            title: promotionTitle,
            items: [],
          }
          // Store the response in static storage.
          skus.forEach((freeGiftSku) => {
            // Traverse through all the products and validate the freeGiftSku
            // with parent and child sku.
            // @todo To use the parent product only to get the free gift
            // details.
            freeGiftProduct.variants.forEach((freeGiftVariant) => {
              if (freeGiftVariant.product.sku === freeGiftSku) {
                window.commerceBackend.setRcsProductToStorage(freeGiftProduct, 'free_gift', freeGiftSku);
              }
            });
            // Prepare the data items.
            var freeGiftItem = window.commerceBackend.getProductData(freeGiftSku, null, false);
            if (freeGiftItem) {
              var freeGiftImage = window.commerceBackend.getFirstImage(freeGiftItem);
              data.items.push({
                title: freeGiftItem.name,
                freeGiftImage: Drupal.hasValue(freeGiftImage.url) ? freeGiftImage.url : '',
                freeGiftSku,
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
      $(".free-gift-back-to-collection").once('back-to-collection-processed').on('click', function (e) {
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
    if (!Drupal.hasValue(freeGiftProduct)) {
      return;
    }

    var elm = document.createElement('div');
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
  };

})(jQuery, Drupal, drupalSettings, RcsEventManager);
