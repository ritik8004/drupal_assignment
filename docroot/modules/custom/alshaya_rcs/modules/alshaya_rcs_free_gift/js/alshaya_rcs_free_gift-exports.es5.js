exports.computePhFilters = function (input, filter) {
  let value = '';
  let data = {};

  switch (filter) {
    case 'promotion_free_gift':
      const freeGiftPromotions = input.free_gift_promotion;
      // Proceed only if we have free gift promotion items.
      // We support displaying only one free gift promotion for now.
      if (freeGiftPromotions.length > 0) {
        const freeGiftPromotion = freeGiftPromotions[0];
        if (freeGiftPromotion.total_items === 0 || freeGiftPromotion.gifts.length === 0) {
          break;
        }
        data.freeGiftType = freeGiftPromotion.rule_type;
        data.freeGiftCoupon = freeGiftPromotion.coupon_code;
        data.freeGiftPromoUrl = Drupal.url(freeGiftPromotion.rule_web_url);
        data.freeGiftTitle = '';
        data.freeGiftImage = '';

        const giftItems = freeGiftPromotion.gifts;
        // Get the free gift sku info.
        const giftItemProductInfo = window.commerceBackend.getProductData(giftItems[0].sku, null, false);
        if (giftItemProductInfo) {
          // Get the first image.
          const skuImage = window.commerceBackend.getFirstImage(giftItemProductInfo);
          data.freeGiftImage = Drupal.hasValue(skuImage)
            ? skuImage.url
            : drupalSettings.alshayaRcs.default_meta_image;
          data.styleCode = giftItemProductInfo.style_code;
        }
        // Set the first free gift title.
        data.freeGiftTitle = giftItems[0].name;

        // Do processing of free gift items.
        // @see Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager::getFreeGiftDisplay().
        if (freeGiftPromotion.total_items > 1
          && freeGiftPromotion.rule_type === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
          // Load all the free gift items.
          var freeGiftSkus = [];
          giftItems.forEach((item) => {
            freeGiftSkus.push(item.sku);
          });
          data.freeGiftSku = freeGiftSkus;
          data.freeGiftPromotionTitle = freeGiftPromotion.rule_name;

          // Render handlebars plugin.
          value = handlebarsRenderer.render(`product.${filter}_list`, data);
        } else {
          const freeGift = giftItems[0];
          data.freeGiftSku = freeGift.sku;

          // Render handlebars plugin.
          value = handlebarsRenderer.render(`product.${filter}`, data);
        }
      }
      break;

    default:
      Drupal.alshayaLogger('debug', 'Unknown JS filter @filter.', { '@filter': filter })
  }

  return value;
};
