/**
 * @file
 * RCS Free gift js common file.
 */

(function ($, Drupal, drupalSettings) {
  // Variables to have collection and collection item dialog globally, so that
  // we can control the modal from all the functions.
  var collectionDialog = '';
  var collectionItemDialog = '';
  window.commerceBackend = window.commerceBackend || {};

  /**
   * Use this Cautiously!!!
   *
   * MDC response for product_by_sku when accessed using simple variant sku
   * of a configurable product, we get the data of parent product sku. Even
   * though we requested product details for variant simple sku.
   * This is a wrapper function which mocks the parent sku response as
   * it was requested for simple sku.
   *
   * @param {string} variantSku
   *   Requested variant sku of a configurable product.
   * @param {object} productData
   *   Configurable product response data received from MDC.
   *
   * @return {object}
   *   Mock simple sku response.
   */
  window.commerceBackend.processProductResponse = function processProductResponse(variantSku, productData) {
    // Don't do anything for simple products.
    if (productData.type_id === 'simple') {
      return productData;
    }
    var requestedVariant = null;
    // Loop through variants to fetch the requested variant sku.
    for (var $i = 0; $i < productData.variants.length; $i++) {
      if (productData.variants[$i].product.sku === variantSku) {
        requestedVariant = productData.variants[$i];
        break;
      }
    }
    if (requestedVariant) {
      // Update product keys from variant data.
      Object.keys(requestedVariant.product).forEach(function updateVariantInfo(key) {
        if (key in productData) {
          productData[key] = requestedVariant.product[key];
        }
      });
      // Manually setting product type to simple.
      productData.type_id = 'simple';
    }
    return productData;
  };

  /**
   * Validates and fetches free gift data from MDC.
   *
   * @param {string} freeGiftSku
   *   Free gift sku.
   *
   * @return {object}
   *   Valid free gift data from MDC.
   */
  window.commerceBackend.fetchValidatedFreeGift = function fetchValidatedFreeGift(freeGiftSku) {
    // On Page load, some free gift data is already cached.
    var freeGiftItem = globalThis.RcsPhStaticStorage.get('product_data_' + freeGiftSku);
    // For uncached data, do api calls.
    if (!Drupal.hasValue(freeGiftItem)) {
      // Synchronous call to get product by skus.
      freeGiftItem = globalThis.rcsPhCommerceBackend.getDataSynchronous('product_by_sku', { sku: freeGiftSku });
      if (Drupal.hasValue(freeGiftItem) && Drupal.hasValue(freeGiftItem.sku)) {
        if (freeGiftItem.type_id === 'configurable') {
          // For configurable products having style code, we will take all styled variants.
          if (Drupal.hasValue(freeGiftItem.style_code) && Drupal.hasValue(window.commerceBackend.getProductsInStyleSynchronous)) {
            freeGiftItem = window.commerceBackend.getProductsInStyleSynchronous({
              sku: freeGiftItem.sku,
              style_code: freeGiftItem.style_code,
              context: 'free_gift'
            });
          }
          if (freeGiftItem.sku !== freeGiftSku) {
            // This condition means freeGiftSku product is actually simple
            // but MDC returns the configurable parent product of a simple sku
            // when product_by_sku api is called.
            // Then we need to alter the response to get simple sku data.
            freeGiftItem = window.commerceBackend.processProductResponse(freeGiftSku, freeGiftItem);
          }
        }
        // Store it in local storage for further usage on different pages.
        window.commerceBackend.setRcsProductToStorage(freeGiftItem, 'free_gift', freeGiftSku);
      }
    }

    return freeGiftItem;
  };

  /**
   * Utility function to show free gift item in dialog box.
   *
   * @param {string} sku
   *   The free gift product sku.
   * @param {boolean} backToCollection
   *   Boolean flag to show the back to collection link.
   */
  window.commerceBackend.showItemModalView = function showItemModalView(sku, backToCollection = false) {
    // Load the product data based on sku.
    var freeGiftProduct = globalThis.RcsPhStaticStorage.get('product_data_' + sku);
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
    // Remove any modal if previously opened.
    $('#drupal-modal').remove();
    modalContext.find('.ui-widget-content').attr('id', 'drupal-modal');

    // For configurable products, show sku base form.
    if (freeGiftProduct.type_id === 'configurable') {
      window.commerceBackend.renderAddToCartForm(freeGiftProduct);
      if (modalContext.find('.sku-base-form').length) {
        modalContext.find('.sku-base-form').removeClass('visually-hidden');
      }
      // Removing style guide modal link inside free gift modal.
      if (modalContext.find('.size-guide-form-and-link-wrapper').length) {
        modalContext.find('.size-guide-link').remove();
      }
      // Removing quantity dropdown modal link inside free gift modal.
      if (modalContext.find('.form-item-quantity').length) {
        modalContext.find('.form-item-quantity').remove();
      }
      // As we don't need the add to cart button, so removing it.
      modalContext.find('button.add-to-cart-button').remove();
    } else {
      // For simple products, just remove the add to cart skeletal.
      modalContext.find('.add_to_cart_form').addClass('rcs-loaded');
    }

    // Call behaviours with modal context.
    globalThis.rcsPhApplyDrupalJs(modalContext);
  };

  /**
   * Utility function to start free gift modal processing and show.
   */
  window.commerceBackend.startFreeGiftModalProcess = function startFreeGiftModalProcess (skus, backToCollection = false) {
    // Do not process if skus are not defined.
    if (!Drupal.hasValue(skus)) {
      return;
    }
    // Display loader.
    if (typeof Drupal.cartNotification.spinner_start === 'function') {
      document.querySelector('body').scrollIntoView({
        behavior: 'smooth',
      });
      Drupal.cartNotification.spinner_start();
    }

    $('body').addClass('free-gifts-modal-overlay');

    // We have two type of modals for free gift.
    // 1. The single item modal ( Where we display a individual free gift )
    // 2. Collection of item modal ( List of free gift items )

    if (skus.length === 1) {
      // Displaying single free gift item modal.
      window.commerceBackend.showItemModalView(skus[0], backToCollection);
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
        var freeGiftItem = window.commerceBackend.fetchValidatedFreeGift(freeGiftSku);
        // Prepare the data items.
        if (freeGiftItem) {
          var freeGiftImage = window.commerceBackend.getFirstImage(freeGiftItem);
          data.items.push({
            title: freeGiftItem.name,
            freeGiftImage: Drupal.hasValue(freeGiftImage) ? freeGiftImage.url : drupalSettings.alshayaRcs.default_meta_image,
            freeGiftImageTitle: freeGiftItem.name,
            freeGiftSku,
            backToCollection: true,
          });
        }
      });

      // If valid sku count is only one after processing, do not render free gift list modal.
      if (data.items.length === 1) {
        // Displaying single free gift item modal.
        window.commerceBackend.showItemModalView(data.items[0].freeGiftSku);
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
      // Remove any modal if previously opened.
      $('#drupal-modal').remove();
      modalContext.find('.ui-widget-content').attr('id', 'drupal-modal');
      globalThis.rcsPhApplyDrupalJs(modalContext);
    }
    // Remove the loader from the screen.
    Drupal.cartNotification.spinner_stop();
  };

  /*
   * Common free gift modal behaviour.
   */
  Drupal.behaviors.rcsFreeGiftsCommon = {
    attach: function (context, settings) {
      // On dialog close remove the free gift overlay related classes.
      $('.free-gifts-modal-overlay #free-gift-drupal-modal').once().on('dialogclose', function () {
        if ($('body').hasClass('free-gift-promo-list-overlay')) {
          $('body').removeClass('free-gift-promo-list-overlay');
        }
        $('body').removeClass('free-gifts-modal-overlay');
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
   * Utility function to alter graphQl product response
   * to support react data structure for free gifts.
   *
   * @param {object} productInfo
   *   The product info from graphQl.
   * @param {boolean} checkForVariants
   *   Flag to check for product variants in case of configurable products.
   */
  window.commerceBackend.processFreeGiftDataReactRender = async function processFreeGiftDataReactRender (productInfo, checkForVariants = true) {
    var skuItemCode = Object.keys(productInfo)[0];
    // If product does not have any free gifts associated, then return.
    if (!Drupal.hasValue(productInfo[skuItemCode].freeGiftPromotion)) {
      return productInfo;
    }
    // Free gift info from API response.
    var freeGiftApiData = productInfo[skuItemCode].freeGiftPromotion[0];
    var promoUrl = Drupal.url(freeGiftApiData.rule_web_url);
    var processedFreeGiftData = {};
    var giftItemProductInfo = window.commerceBackend.fetchValidatedFreeGift(freeGiftApiData.gifts[0].sku);
    var skuImage = window.commerceBackend.getFirstImage(giftItemProductInfo);
    var skuImageUrl = Drupal.hasValue(skuImage) ? skuImage.url : drupalSettings.alshayaRcs.default_meta_image;
    if (freeGiftApiData.total_items > 1
      && freeGiftApiData.rule_type === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
      // Load all the free gift items.
      var freeGiftSkus = [];
      freeGiftApiData.gifts.forEach((item) => {
        freeGiftSkus.push(item.sku);
      });
      var data = {
        freeGiftPromoUrl: promoUrl,
        freeGiftSku: freeGiftSkus,
        freeGiftPromotionTitle: freeGiftApiData.rule_name,
        styleCode: giftItemProductInfo.style_code,
      };
      processedFreeGiftData['#theme'] = 'free_gift_promotion_list';
      processedFreeGiftData['#message'] = [];
      processedFreeGiftData['#message']['#type'] = 'markup';
      processedFreeGiftData['#message']['#markup'] = handlebarsRenderer.render('product.promotion_free_gift_message_sub_type_one_sku', data);
      processedFreeGiftData['#title'] = [];
      processedFreeGiftData['#title']['#type'] = 'markup';
      processedFreeGiftData['#title']['#markup'] = freeGiftApiData.rule_name;
      processedFreeGiftData['#promo_code'] = freeGiftApiData.coupon_code;
      processedFreeGiftData['#free_sku_code'] = freeGiftSkus.toString();
      processedFreeGiftData['#free_sku_type'] = 'simple';
      processedFreeGiftData['#image'] = [];
      processedFreeGiftData['#image']['#theme'] = 'image';
      processedFreeGiftData['#image']['#uri'] = skuImageUrl;
      processedFreeGiftData['#image']['#url'] = skuImageUrl;
      processedFreeGiftData['#image']['#attributes'] = [];
      processedFreeGiftData['#image']['#attributes'].src = skuImageUrl;
      processedFreeGiftData['#image']['#attributes'].title = freeGiftApiData.gifts[0].name;
      processedFreeGiftData['#image']['#attributes'].alt = freeGiftApiData.gifts[0].name;
      processedFreeGiftData['#promo_type'] = freeGiftApiData.rule_type;
      processedFreeGiftData['#coupon'] = [];
      processedFreeGiftData['#coupon']['#type'] = 'markup';
      processedFreeGiftData['#coupon']['#markup'] = handlebarsRenderer.render('product.promotion_free_gift_message_coupon_code', { freeGiftCoupon: freeGiftApiData.coupon_code });
      processedFreeGiftData.coupon = freeGiftApiData.coupon_code;
      processedFreeGiftData.promo_title = freeGiftApiData.rule_name;
      processedFreeGiftData.promo_web_url = promoUrl;
    } else if (freeGiftApiData.total_items > 0) {
      processedFreeGiftData['#theme'] = 'free_gift_promotions';
      processedFreeGiftData['#free_sku_entity_id'] = freeGiftApiData.gifts[0].id;
      processedFreeGiftData['#free_sku_code'] = freeGiftApiData.gifts[0].sku;
      processedFreeGiftData['#free_sku_type'] = 'simple';
      processedFreeGiftData['#free_sku_title'] = handlebarsRenderer.render('product.promotion_free_gift_message_sub_type_all_sku', {
        freeGiftPromoUrl: promoUrl,
        freeGiftSku: freeGiftApiData.gifts[0].sku,
        freeGiftTitle: freeGiftApiData.gifts[0].name
      });
      processedFreeGiftData['#free_sku_title_raw'] = freeGiftApiData.gifts[0].name;
      processedFreeGiftData['#promo_title'] = freeGiftApiData.rule_name;
      processedFreeGiftData['#promo_code'] = [{value: freeGiftApiData.coupon_code}];
      processedFreeGiftData['#promo_type'] = freeGiftApiData.rule_type;
      processedFreeGiftData['#sku_image'] = handlebarsRenderer.render('product.promotion_free_gift_message_sub_type_all_sku', {
        freeGiftPromoUrl: promoUrl,
        freeGiftSku: freeGiftApiData.gifts[0].sku,
        freeGiftTitle: freeGiftApiData.gifts[0].name,
        freeGiftImage: skuImageUrl
      });
      processedFreeGiftData.coupon = freeGiftApiData.coupon_code;
      processedFreeGiftData.promo_title = freeGiftApiData.rule_name;
      processedFreeGiftData.promo_web_url = promoUrl;
    }
    // Updating free gift promotion response from graphQl with react processable data.
    productInfo[skuItemCode].freeGiftPromotion = processedFreeGiftData;

    // For configurable products, also need to update all variant data with processed free gift data.
    if (checkForVariants
      && productInfo[skuItemCode].type === 'configurable'
      && Drupal.hasValue(productInfo[skuItemCode].variants)
    ) {
      // Shorthand variable to process variants.
      var variants = productInfo[skuItemCode].variants;
      // Loop through each variant.
      for (const variantSku of Object.keys(variants)) {
        // Skip if variant does not have free gift.
        if (Drupal.hasValue(variants[variantSku].freeGiftPromotion)) {
          variants[variantSku].skuItemCode = variantSku;
          // Recursive call to same function to process free gift data for variants in exactly same fashion.
          var variantData = await window.commerceBackend.processFreeGiftDataReactRender(
            {
              [variantSku]: variants[variantSku],
              skuItemCode: variantSku,
            },
            false
          );
          // Updating free gift promotion response from graphQl with react processable data for variants.
          productInfo[skuItemCode].variants[variantSku] = variantData[variantSku];
        }
      }
    }

    return productInfo;
  };

})(jQuery, Drupal, drupalSettings);
