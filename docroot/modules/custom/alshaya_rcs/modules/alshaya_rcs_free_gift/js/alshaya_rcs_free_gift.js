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
   * Validates and fetches free gift data from MDC.
   *
   * @param {string} freeGiftSku
   *   Free gift sku.
   *
   * @return {object}
   *   Valid free gift data from MDC.
   */
  function fetchValidatedFreeGift(freeGiftSku) {
    // On Page load, some free gift data is already cached.
    var freeGiftItem = globalThis.RcsPhStaticStorage.get('product_data_' + freeGiftSku);
    // For uncached data, do api calls.
    if (!Drupal.hasValue(freeGiftItem)) {
      // Synchronous call to get product by skus.
      freeGiftItem = globalThis.rcsPhCommerceBackend.getDataSynchronous('product_by_sku', { sku: freeGiftSku });
      if (Drupal.hasValue(freeGiftItem) && Drupal.hasValue(freeGiftItem.sku)) {
        if (freeGiftItem.type_id === 'configurable' && Drupal.hasValue(freeGiftItem.style_code) && Drupal.hasValue(window.commerceBackend.getProductsInStyleSynchronus)) {
          // For configurable SKUs, we only expect sku of parent free gift as freeGiftSku in api response.
          if (freeGiftItem.sku !== freeGiftSku) {
            return null;
          }
          // Again for configurable products having style code, we will take the in style product.
          freeGiftItem = window.commerceBackend.getProductsInStyleSynchronus({ sku: freeGiftItem.sku, style_code: freeGiftItem.style_code });
        }
        // Store it in local storage for further usage on different pages.
        window.commerceBackend.setRcsProductToStorage(freeGiftItem, 'free_gift', freeGiftSku);
      }
    }

    return freeGiftItem;
  }

  /**
   * Event listener of page entity to get the free gift product info.
   */
  RcsEventManager.addListener('alshayaPageEntityLoaded', async function pageEntityLoaded(e) {
    var mainProduct = e.detail.entity;
    // Get the list of all the available Free gifts.
    var freeGiftPromotion = mainProduct.free_gift_promotion;
    // We support displaying only one free gift promotion for now.
    if (freeGiftPromotion.length > 0 && freeGiftPromotion[0].total_items > 0) {
      var giftItemList = freeGiftPromotion[0].gifts;
      var freeGiftProduct = null;
      for (var i = 0; i < giftItemList.length; i++) {
        // Fetch first valid free gift data.
        freeGiftProduct = fetchValidatedFreeGift(giftItemList[i].sku);
        if (Drupal.hasValue(freeGiftProduct)) {
          // If a valid free gift found, break. AS we will only cache 1 free gift data.
          // For multiple free gifts, we will load the free gift product info during modal view.
          // To save PDP render time.
          break;
        } else {
          // If its not a valid free gift, delete it from response.
          // And continue looking for next valid free gift sku.
          delete giftItemList[i];
        }
      }

      mainProduct.free_gift_promotion[0].gifts = giftItemList.flat();
      // Render if at least one valid free gift found.
      if (mainProduct.free_gift_promotion[0].gifts.length) {
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
          // Displaying single free gift item modal.
          showItemModalView(freeGiftProduct, skus[0], backToCollection);
        } else if (skus.length > 1) {
          // Get the free gift promotion title.
          var promotionTitle = $(this).data('promotion-title');
          var elm = document.createElement('div');
          var data = {
            title: promotionTitle,
            items: [],
          };
          skus.forEach(function processMultipleFreeGiftSkus(freeGiftSku) {
            // Fetch free gift data from MDC.
            var freeGiftItem = fetchValidatedFreeGift(freeGiftSku);
            // Prepare the data items.
            if (freeGiftItem) {
              var freeGiftImage = window.commerceBackend.getFirstImage(freeGiftItem);
              data.items.push({
                title: freeGiftItem.name,
                freeGiftImage: Drupal.hasValue(freeGiftImage) ? freeGiftImage.url : drupalSettings.alshayaRcs.default_meta_image,
                freeGiftImageTitle: freeGiftItem.name,
                freeGiftSku,
                backToCollection: true,
                freeGiftItem,
              });
            }
          });

          // If valid sku count is only one after processing, do not render free gift list modal.
          if (data.items.length === 1) {
            // Displaying single free gift item modal.
            showItemModalView(data.items[0].freeGiftItem, data.items[0].freeGiftSku);
            Drupal.cartNotification.spinner_stop();
            return;
          }

          // Collection of item modal.
          elm.innerHTML = handlebarsRenderer.render('product.promotion_free_gift_items', data);

          // Open modal.
          collectionDialog = Drupal.dialog(elm, {
            dialogClass: 'pdp-modal-box',
            autoResize: false,
            closeOnEscape: true,
            width: 'auto',
            close: function close() {
              $('.pdp-modal-box').remove();
            },
          });
          collectionDialog.show();

          // Call behaviours with modal context.
          var modalContext = $('.pdp-modal-box');
          modalContext.find('.ui-widget-content').attr('id', 'drupal-modal');
          globalThis.rcsPhApplyDrupalJs(modalContext);
        }
        // Remove the loader from the screen.
        Drupal.cartNotification.spinner_stop();
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
  };

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
    // The single item modal.
    elm.innerHTML = handlebarsRenderer.render('product.promotion_free_gift_modal', data);
    // Show the modal.
    collectionItemDialog = Drupal.dialog(elm, {
      dialogClass: 'pdp-modal-box',
      autoResize: false,
      closeOnEscape: true,
      width: 'auto',
      close: function close() {
        $('.pdp-modal-box').remove();
      },
    });
    collectionItemDialog.show();

    var modalContext = $('.pdp-modal-box');
    modalContext.find('.ui-widget-content').attr('id', 'drupal-modal');

    // For configurable products, show sku base form.
    if (freeGiftProduct.type_id === 'configurable') {
      window.commerceBackend.renderAddToCartForm(freeGiftProduct);
      // Removing style guide modal link inside free gift modal.
      if (modalContext.find('.size-guide-form-and-link-wrapper').length) {
        $('.pdp-modal-box .size-guide-link').remove();
      }
      // Removing quantity dropdown modal link inside free gift modal.
      if (modalContext.find('.form-item-quantity').length > 0) {
        $('.pdp-modal-box .form-item-quantity').remove();
      }
      // As we don't need the add to cart button, so removing it.
      modalContext.find('button.add-to-cart-button').remove();
    } else {
      // For simple products, just remove the add to cart skeletal.
      modalContext.find('.add_to_cart_form').addClass('rcs-loaded');
    }

    // Call behaviours with modal context.
    globalThis.rcsPhApplyDrupalJs(modalContext);
  }

  /**
   * Utility function to alter graphQl product response
   * to support magazine V2 react data structure for free gifts.
   *
   * @param {object} productInfo
   *   The product info from graphQl.
   * @param {boolean} checkForVariants
   *   Flag to check for product variants in case of configurable products.
   */
  window.commerceBackend.processFreeGiftDataMagV2 = function (productInfo, checkForVariants = true) {
    var skuItemCode = Object.keys(productInfo)[0];
    // If product does not have any free gifts associated, then return.
    if (!Drupal.hasValue(productInfo[skuItemCode].freeGiftPromotion)) {
      return productInfo;
    }
    // Shorthand variable to store free gift info from graphQl response.
    var freeGiftApiData = productInfo[skuItemCode].freeGiftPromotion[0];
    // Variable to store processed free gift information.
    var processedData = {};

    var freeGiftImageUrl = '';
    var freeGiftImageAlt = '';
    var giftItemStyleCode = '';
    // Free gift SKU details.
    var giftItemProductInfo = window.commerceBackend.getProductData(freeGiftApiData.gifts[0].sku, null, false);
    if (giftItemProductInfo) {
      // Get the first image.
      var skuImage = window.commerceBackend.getFirstImage(giftItemProductInfo);
      giftItemStyleCode = giftItemProductInfo.style_code;
      // Building free gift image details.
      freeGiftImageUrl = drupalSettings.alshayaRcs.default_meta_image;
      freeGiftImageAlt = Drupal.t('Free Gift');
      if (Drupal.hasValue(skuImage)) {
        freeGiftImageUrl = skuImage.url;
        freeGiftImageAlt = freeGiftApiData.gifts[0].name;
      }
    }

    // Building processedData for react.
    processedData['#promo_type'] = freeGiftApiData.rule_type;
    processedData['#promo_code'] = Drupal.hasValue(freeGiftApiData.coupon_code) ? [{ value: freeGiftApiData.coupon_code }] : [];

    var data = {
      url: Drupal.url(freeGiftApiData.rule_web_url),
      freeGiftSku: freeGiftApiData.gifts.map(function(gift) {return gift.sku;}).join(','),
      styleCode: giftItemStyleCode,
      promoTitle: freeGiftApiData.rule_name,
      title: freeGiftImageAlt,
    };
    // Free gift title markup to be passed to react for rendering.
    var skuTitleMarkup = handlebarsRenderer.render('product.promotion_free_gift_item', data);

    if (freeGiftApiData.rule_type === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
      processedData['#image'] = [];
      processedData['#image']['#url'] = freeGiftImageUrl;
      processedData['#image']['#alt'] = freeGiftImageAlt;
      processedData['#image']['#title'] = freeGiftImageAlt;
      processedData.promo_title = freeGiftApiData.rule_name;
      processedData['#promo_web_url'] = freeGiftApiData.rule_web_url;
      processedData['#message'] = [];
      processedData['#message']['#markup'] = skuTitleMarkup;
    } else {
      delete data.title;
      data.freeGiftImage = freeGiftImageUrl;
      data.freeGiftImageTitle = freeGiftImageAlt;
      // Free gift image markup to be passed to react for rendering.
      processedData['#sku_image'] = handlebarsRenderer.render('product.promotion_free_gift_item', data);
      processedData['#free_sku_title'] = skuTitleMarkup;
    }
    // Updating free gift promotion response from graphQl with react processable data.
    productInfo[skuItemCode].freeGiftPromotion = processedData;

    // For configurable products, also need to update all variant data with processed free gift data.
    if (checkForVariants
      && productInfo[skuItemCode].type === 'configurable'
      && Drupal.hasValue(productInfo[skuItemCode].variants)
    ) {
      // Shorthand variable to process variants.
      var variants = productInfo[skuItemCode].variants;
      // Loop through each variant.
      Object.keys(variants).forEach(function(variantSku) {
        // Skip if variant does not have free gift.
        if (Drupal.hasValue(variants[variantSku].freeGiftPromotion)) {
          variants[variantSku].skuItemCode = variantSku;
          // Recursive call to same function to process free gift data for variants in exactly same fashion.
          var variantData = window.commerceBackend.processFreeGiftDataMagV2(
            {
              [variantSku]: variants[variantSku],
              skuItemCode: variantSku,
            },
            false
          );
          // Updating free gift promotion response from graphQl with react processable data for variants.
          productInfo[skuItemCode].variants[variantSku] = variantData[variantSku];
        }
      });
    }

    return productInfo;
  };

})(jQuery, Drupal, drupalSettings, RcsEventManager);
