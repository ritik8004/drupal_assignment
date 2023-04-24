/**
 * @file
 * RCS Free gift js common file.
 */

window.commerceBackend = window.commerceBackend || {};

(function alshayaRcsFreeGiftCommon($, Drupal, drupalSettings) {
  // Variables to have collection and collection item dialog globally, so that
  // we can control the modal from all the functions.
  var collectionDialog = '';
  var collectionItemDialog = '';

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
      // Storing the parent sku data for further usage if needed.
      productData.parent_sku = productData.sku;
      // Update product keys from variant data.
      Object.keys(requestedVariant.product).forEach(function updateVariantInfo(key) {
        if (key in productData) {
          productData[key] = requestedVariant.product[key];
        }
      });
      // Manually setting product type to simple.
      productData.type_id = 'simple';
      // Deleting configurable attributes.
      delete productData.configurable_options;
      delete productData.variants;
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
  window.commerceBackend.fetchValidFreeGift = async function fetchValidFreeGift(freeGiftSku) {
    // On Page load, some free gift data is already cached.
    var freeGiftItem = globalThis.RcsPhStaticStorage.get('product_data_' + freeGiftSku);
    // For uncached data, do api calls.
    if (!Drupal.hasValue(freeGiftItem)) {
      // Synchronous call to get product by skus.
      freeGiftItem = await globalThis.rcsPhCommerceBackend.getData('product_by_sku', { sku: freeGiftSku });
      if (Drupal.hasValue(freeGiftItem) && Drupal.hasValue(freeGiftItem.sku)) {
        if (freeGiftItem.type_id === 'configurable') {
          if (freeGiftItem.sku !== freeGiftSku) {
            // This condition means freeGiftSku product is actually simple
            // but MDC returns the configurable parent product of a simple sku
            // when product_by_sku api is called.
            // Then we need to alter the response to get simple sku data.
            freeGiftItem = window.commerceBackend.processProductResponse(freeGiftSku, freeGiftItem);
            // Process free gift media.
            window.commerceBackend.setMediaData(freeGiftItem);
          }
          else {
            // For configurable products having style code, we will take all styled variants.
            if (Drupal.hasValue(freeGiftItem.style_code) && Drupal.hasValue(window.commerceBackend.getProductsInStyle)) {
              freeGiftItem.context = 'modal';
              freeGiftItem = await window.commerceBackend.getProductsInStyle(freeGiftItem);
            }
            else {
              // Process free gift media.
              window.commerceBackend.setMediaData(freeGiftItem);
            }
          }
        }
        else {
          // Process free gift media.
          window.commerceBackend.setMediaData(freeGiftItem);
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
   * @param {string} couponCode
   *   Free gift promotion coupon code.
   * @param {string} promoRuleId
   *   Free gift promotion rule id.
   */
  window.commerceBackend.showItemModalView = async function showItemModalView(sku, backToCollection = false, couponCode = null, promoRuleId = null) {
    // Load the product data based on sku.
    var freeGiftProduct = await window.commerceBackend.fetchValidFreeGift(sku);

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

    freeGiftProduct.context = 'free_gift';
    freeGiftProduct.couponCode = couponCode;
    freeGiftProduct.promoRuleId = promoRuleId;
    // Rendering sku base form inside modal.
    window.commerceBackend.renderAddToCartForm(freeGiftProduct);
    // For simple products, remove configurable options section if exists.
    if (freeGiftProduct.type_id === 'simple' && modalContext.find('#configurable_ajax').length) {
      modalContext.find('#configurable_ajax').remove();
    }
    // Only show add free gift button in cart page.
    if (drupalSettings.path.currentPath !== 'cart') {
      modalContext.find('button#add-free-gift').remove();
    }

    // Call behaviours with modal context.
    globalThis.rcsPhApplyDrupalJs(modalContext);
  };

  /**
   * Utility function to start free gift modal processing and show.
   */
  window.commerceBackend.startFreeGiftModalProcess = async function startFreeGiftModalProcess (skus, backToCollection = false, couponCode = '') {
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
    var promotionTitle; var promoRuleId;

    if (drupalSettings.path.currentPath === 'cart') {
      promotionTitle = $('.free-gift-title').text();
      promoRuleId = $('.free-gift-promo .gift-message a').data('promo-rule-id');
    }
    else {
      promotionTitle = $('.free-gift-message a.free-gift-modal').data('promotion-title');
    }

    // We have two type of modals for free gift.
    // 1. The single item modal ( Where we display a individual free gift )
    // 2. Collection of item modal ( List of free gift items )
    if (skus.length === 1) {
      // Displaying single free gift item modal.
      await window.commerceBackend.showItemModalView(skus[0], backToCollection, couponCode, promoRuleId);
    } else if (skus.length > 1) {
      var elm = document.createElement('div');
      var data = {
        title: promotionTitle,
        items: [],
      };
      for (var freeGiftSku of skus) {
        // Fetch free gift sku data from MDC.
        var freeGiftItem = await window.commerceBackend.fetchValidFreeGift(freeGiftSku);
        // Prepare the data items.
        if (freeGiftItem) {
          var freeGiftImage = window.commerceBackend.getFirstImage(freeGiftItem);
          data.items.push({
            title: freeGiftItem.name,
            freeGiftImage: Drupal.hasValue(freeGiftImage) ? freeGiftImage.url : drupalSettings.alshayaRcs.default_meta_image,
            freeGiftImageTitle: freeGiftItem.name,
            freeGiftSku,
            freeGiftParentSku: Drupal.hasValue(freeGiftItem.parent_sku) ? freeGiftItem.parent_sku : freeGiftSku,
            backToCollection: true,
            freeGiftItem,
            couponCode,
            promoRuleId,
            selectLink: drupalSettings.path.currentPath === 'cart'
          });
        }
      }

      // If valid sku count is only one after processing, do not render free gift list modal.
      if (data.items.length === 1) {
        // Displaying single free gift item modal.
        await window.commerceBackend.showItemModalView(data.items[0].freeGiftSku, couponCode, promoRuleId);
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
    attach: function rcsFreeGiftsCommon(context, settings) {
      // Open the collection list on click of back to collection.
      $('.free-gift-back-to-collection').once('back-to-collection-processed').on('click', function (e) {
        e.preventDefault();
        // Close the item dialog box.
        if (collectionItemDialog) {
          collectionItemDialog.close();
        }
        // Opening free gift list modal on clicking back to collection.
        if (drupalSettings.path.currentPath === 'cart') {
          $('.spc-promotions .coupon-code').click();
        } else {
          $('.free-gift-message a').click();
        }
      });
    }
  };

  /**
   * Utility function to alter graphQl product response
   * to support react data structure for free gifts.
   *
   * @param {object} productInfo
   *   The product info from graphQl.
   * @param {string} skuItemCode
   *   SKU of the product.
   * @param {boolean} checkForVariants
   *   Flag to check for product variants in case of configurable products.
   */
  window.commerceBackend.processFreeGiftDataReactRender = async function processFreeGiftDataReactRender (productInfo, skuItemCode, checkForVariants = true) {
    try {
      // If product does not have any free gifts associated, then return.
      if (!Drupal.hasValue(productInfo[skuItemCode]) || !Drupal.hasValue(productInfo[skuItemCode].freeGiftPromotion)) {
        return productInfo;
      }
      // Free gift info from API response.
      var freeGiftApiData = productInfo[skuItemCode].freeGiftPromotion[0];
      var promoUrl = Drupal.url(freeGiftApiData.rule_web_url);
      var processedFreeGiftData = {};
      var giftItemProductInfo = await window.commerceBackend.fetchValidFreeGift(freeGiftApiData.gifts[0].sku);
      if (!Drupal.hasValue(giftItemProductInfo)) {
        productInfo[skuItemCode].freeGiftPromotion = [];
        return productInfo;
      }
      var skuImage = window.commerceBackend.getFirstImage(giftItemProductInfo);
      var skuImageUrl = Drupal.hasValue(skuImage) ? skuImage.url : drupalSettings.alshayaRcs.default_meta_image;
      if (freeGiftApiData.total_items > 1
        && freeGiftApiData.rule_type === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
        // Load all the free gift items.
        var freeGiftSkus = [];
        freeGiftApiData.gifts.forEach(function listFreeGiftSkus(item) {
          freeGiftSkus.push(item.sku);
        });
        var data = {
          freeGiftPromoUrl: promoUrl,
          freeGiftSku: freeGiftSkus,
          freeGiftPromotionTitle: freeGiftApiData.rule_name,
          styleCode: giftItemProductInfo.style_code,
        };
        processedFreeGiftData = {
          '#theme': 'free_gift_promotion_list',
          '#message': {
            '#type': 'markup',
            '#markup': handlebarsRenderer.render('product.promotion_free_gift_message_sub_type_one_sku', data),
          },
          '#title': {
            '#type': 'markup',
            '#markup': freeGiftApiData.rule_name,
          },
          '#promo_code': freeGiftApiData.coupon_code,
          '#free_sku_code': freeGiftSkus.toString(),
          '#image': {
            '#theme': 'image',
            '#uri': skuImageUrl,
            '#url': skuImageUrl,
            '#attributes': {
              src: skuImageUrl,
              title: freeGiftApiData.gifts[0].name,
              alt: freeGiftApiData.gifts[0].name,
            },
          },
          '#coupon': {
            '#type': 'markup',
            '#markup': handlebarsRenderer.render('product.promotion_free_gift_message_coupon_code', {freeGiftCoupon: freeGiftApiData.coupon_code})
          },
        };
      } else if (freeGiftApiData.total_items > 0) {
        processedFreeGiftData = {
          '#theme': 'free_gift_promotions',
          '#free_sku_entity_id': freeGiftApiData.gifts[0].id,
          '#free_sku_code': freeGiftApiData.gifts[0].sku,
          '#free_sku_title': handlebarsRenderer.render('product.promotion_free_gift_message_sub_type_all_sku', {
            freeGiftPromoUrl: promoUrl,
            freeGiftSku: freeGiftApiData.gifts[0].sku,
            freeGiftTitle: freeGiftApiData.gifts[0].name
          }),
          '#free_sku_title_raw': freeGiftApiData.gifts[0].name,
          '#promo_title': freeGiftApiData.rule_name,
          '#promo_code': [{value: freeGiftApiData.coupon_code}],
          '#sku_image': handlebarsRenderer.render('product.promotion_free_gift_message_sub_type_all_sku', {
            freeGiftPromoUrl: promoUrl,
            freeGiftSku: freeGiftApiData.gifts[0].sku,
            freeGiftTitle: freeGiftApiData.gifts[0].name,
            freeGiftImage: skuImageUrl
          }),
        };
      }
      processedFreeGiftData.coupon = freeGiftApiData.coupon_code;
      processedFreeGiftData.promo_title = freeGiftApiData.rule_name;
      processedFreeGiftData.promoRuleId = freeGiftApiData.rule_id;
      processedFreeGiftData.promo_web_url = promoUrl;
      processedFreeGiftData['#free_sku_type'] = giftItemProductInfo.type_id;
      processedFreeGiftData['#promo_type'] = freeGiftApiData.rule_type;

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
        for (var variantSku of Object.keys(variants)) {
          // Skip if variant does not have free gift.
          if (Drupal.hasValue(variants[variantSku].freeGiftPromotion)) {
            variants[variantSku].skuItemCode = variantSku;
            // Recursive call to same function to process free gift data for variants in exactly same fashion.
            var variantData = await window.commerceBackend.processFreeGiftDataReactRender(
              {
                [variantSku]: variants[variantSku],
              },
              variantSku,
              false
            );
            // Updating free gift promotion response from graphQl with react processable data for variants.
            productInfo[skuItemCode].variants[variantSku] = variantData[variantSku];
          }
        }
      }

      return productInfo;
    } catch (e) {
      Drupal.logJavascriptError('free-gift-api-response-processing-error', e);
      return productInfo;
    }
  };

})(jQuery, Drupal, drupalSettings);
