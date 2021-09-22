// Render function to prepare markup for promotional banner.
exports.render = function render(
  settings,
  entity,
  innerHtml
) {
  // Proceed only if entity is present.
  if (entity !== null) {
    let { promotion_banner, promotion_banner_mobile, promo_banner_for_mobile } = entity;
    // Logic to update the promotion banner and promotion banner mobile if any
    // one of them is not present.
    if (promotion_banner && !promotion_banner_mobile) {
      promotion_banner_mobile = promotion_banner;
    }
    else if (!promotion_banner && promotion_banner_mobile) {
      promotion_banner = promo_banner_for_mobile;
    }
    // Update the entity object.
    entity.promotion_banner = promotion_banner;
    entity.promo_banner_for_mobile = promotion_banner_mobile;
    // Add hide on mobile class based on API response.
    entity.classes = promo_banner_for_mobile ? '' : 'hide-on-mobile';

    innerHtml = replacePromoBannerPlaceHolders(entity, innerHtml, settings);
  }

  return innerHtml;
}

/**
 * Replace the placeholders with the Promotional Banner block items.
 *
 * @param {object} entity
 *   The entity object containing category info.
 * @param {string} itemHtml
 *   The Promotional Banner HTML with Placeholders.
 * @param {object} settings
 *   The drupalSettings object.
 * @returns
 *   {string} Promotional Banner HTML with proper data.
 */
const replacePromoBannerPlaceHolders = function (entity, itemHtml, settings) {
  rcsPhReplaceEntityPh(itemHtml, 'category_promo', entity, settings.path.currentLanguage)
    .forEach(function eachReplacement(r) {
      const fieldPh = r[0];
      const entityFieldValue = r[1];
      // Apply the replacement on all the elements containing the
      // placeholder. We filter to keep only the child element
      // and not the parent ones.
      itemHtml = rcsReplaceAll(itemHtml, fieldPh, entityFieldValue);
    });

  return itemHtml;
}
