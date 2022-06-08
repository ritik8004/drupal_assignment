/**
 * Check if the product is buyable.
 *
 * @param {object} entity
 *   The product entity.
 *
 * @returns {Boolean}
 *   Returns true/false if product is buyable/not buyable.
 */
function isProductBuyable(entity) {
  return drupalSettings.alshayaRcs.isAllProductsBuyable || parseInt(entity.is_buyable, 10);
}

/**
 * Create short text with ellipsis and Read more button.
 *
 * @param {string} value
 *   The field value.
 *
 * @returns {object}
 *   Returns the object containing the value and ellipsis information.
 */
function applyEllipsis(value) {
  const limit = drupalSettings.alshayaRcs.shortDescLimit;
  let read_more = false;

  // Strip html tags.
  value = jQuery('<p>' + value + '</p>').text();

  if (value.length > limit) {
    value = value.slice(0, limit) + '...';
    read_more = true;
  }

  return {
    value: value,
    read_more: read_more,
  };
}

/**
 * Gets legal notice value from config.
 *
 * @returns {object}
 *   Object containing the legal notice details.
 */
function getLegalNotice() {
  return {
    status: Drupal.hasValue(drupalSettings.alshayaRcs.legalNotice.status)
      ? drupalSettings.alshayaRcs.legalNotice.status
      : false,
    label: Drupal.hasValue(drupalSettings.alshayaRcs.legalNotice.label)
      ? drupalSettings.alshayaRcs.legalNotice.label
      : '',
    summary: Drupal.hasValue(drupalSettings.alshayaRcs.legalNotice.summary)
      ? drupalSettings.alshayaRcs.legalNotice.summary.value
      : '',
  };
}

/**
 * Gets additional PDP description value from config.
 *
 * @returns {object}
 *   Object containing the additional description.
 */
function getAdditionalPdpDescription() {
  return {
    status: Drupal.hasValue(drupalSettings.alshayaRcs.additionalPdpDescription.status)
      ? drupalSettings.alshayaRcs.additionalPdpDescription.status
      : false,
    label: Drupal.hasValue(drupalSettings.alshayaRcs.additionalPdpDescription.label)
      ? drupalSettings.alshayaRcs.additionalPdpDescription.label
      : '',
    summary: Drupal.hasValue(drupalSettings.alshayaRcs.additionalPdpDescription.summary)
      ? drupalSettings.alshayaRcs.additionalPdpDescription.summary.value
      : '',
  };
}

/**
 * Replace placeholders and get related products.
 *
 * @param {object} products
 *   The product object.
 * @param {string} sectionTitle
 *   The translated title for related, upsell.
 *
 * @returns {*}
 *   Html with placeholders replaced.
 */
function getProductRecommendation(products, sectionTitle) {
  // Create the containers for carousel.
  const related = jQuery('<div />');
  related.append(jQuery('.rcs-templates--related-product-wrapper').html());
  related.find('.subtitle').html(sectionTitle);

  // Get Product teaser template with tokens.
  const productTeaser = jQuery('.rcs-templates--product-teaser').html();

  // Replace tokens and add teaser to the container.
  let finalMarkup = '';
  products.forEach((product, index) => {
    related.find('.owl-carousel').append('<div id="row' + index + '" class="views-row"/>');
    related.find('#row' + index).append(productTeaser);
    const attributes = globalThis.rcsPhGetSetting('placeholderAttributes');
    finalMarkup = related.html();
    rcsPhReplaceEntityPh(finalMarkup, 'product_teaser', product, drupalSettings.path.currentLanguage)
      .forEach(function eachReplacement(r) {
        const fieldPh = r[0];
        const entityFieldValue = r[1];
        finalMarkup = globalThis.rcsReplaceAll(finalMarkup, fieldPh, entityFieldValue);
      });
    related.html(finalMarkup);
  });

  return related.html();
}

/**
 * Get the amount with the proper format for decimals.
 *
 * @param priceAmount
 *   The price amount.
 *
 * @returns {string|*}
 *   Return string with price and currency or return array of price and
 *   currency.
 */
function getFormattedAmount(priceAmount) {
  let amount = priceAmount === null ? 0 : priceAmount;

  // Remove commas if any.
  amount = amount.toString().replace(/,/g, '');
  amount = !Number.isNaN(Number(amount)) === true ? parseFloat(amount) : 0;

  return amount.toFixed(drupalSettings.alshaya_spc.currency_config.decimal_points);
};
exports.getFormattedAmount = getFormattedAmount;

/**
 * Get SKU based on attribute option id.
 *
 * @param {string} $sku
 *   The parent sku value.
 * @param {string} attribute
 *   Attribute to search for.
 * @param {Number} option_id
 *   Option id for selected attribute.
 *
 * @return {string}
 *   SKU value matching the attribute option id.
 */
function getChildSkuFromAttribute(sku, attribute, option_id) {
  const combinations = window.commerceBackend.getConfigurableCombinations(sku);

  if (!Drupal.hasValue(combinations.attribute_sku[attribute][option_id])) {
    Drupal.alshayaLogger('warning', 'No combination available for attribute @attribute and option @option_id for SKU @sku', {
      '@attribute': attribute,
      '@option_id': option_id,
      '@sku': sku
    });
    return null;
  }

  return combinations.attribute_sku[attribute][option_id][0];
}

/**
 * Get the swatch image url for the provided sku.
 *
 * @param {string} sku
 *   The SKU value.
 *
 * @returns {string}
 *   The swatch image url.
 */
function getPdpSwatchImageUrl(product, childSku) {
  let swatchImageUrl = null;
  product.variants.forEach(function (variant) {
    if (variant.product.sku == childSku) {
      swatchImageUrl = variant.product.media.swatch;
      // Break from the loop.
      return false;
    }
  });

  return swatchImageUrl;
}

/**
 * Removes unavailable option values from provided configurable options.
 *
 * @param {string} sku
 *   SKU value.
 * @param {object} configurableOptions
 *   Configurable options of the product.
 *
 * @returns {Array}
 *   The configurable options minus the unavailable values.
 */
function disableUnavailableOptions(sku, configurableOptions) {
  const combinations = window.commerceBackend.getConfigurableCombinations(sku);
  // Clone this so as to not modify the original object.
  configurableOptionsClone = JSON.parse(JSON.stringify(configurableOptions));
  configurableOptionsClone.forEach(function eachOption(option) {
    option.values.forEach(function eachValue(value, index) {
      if (typeof combinations.attribute_sku[option.attribute_code][value.value_index] === 'undefined') {
        option.values.splice(index, 1);
      }
    });
  });

  return configurableOptionsClone;
}

exports.render = function render(
  settings,
  placeholder,
  params,
  inputs,
  entity,
  langcode,
  innerHtml
) {
  let html = "";
  switch (placeholder) {
    case "delivery-info-block":
      if (!isProductBuyable(entity)) {
        break;
      }

      // Add express delivery options that are available on product entity.
      const deliveryInfo = {
        delivery_in_only_city_text: drupalSettings.alshayaRcs.pdp.delivery_in_only_city_text,
        expressDelivery: [],
        sameDayDelivery: {
          text: null,
          sub_text: null,
        }
      };

      // Express delivery.
     drupalSettings.alshayaRcs.pdp.expressDelivery.forEach(function (option, i) {
        option.class = (option.status && Drupal.hasValue(entity[option.id]))
          ? 'active'
          : 'in-active';
        deliveryInfo.expressDelivery.push(option);
      });

      // Same day delivery.
      if (Drupal.hasValue(entity.same_day_delivery)) {
        deliveryInfo.sameDayDelivery = drupalSettings.alshayaRcs.pdp.sameDayDelivery;
      }

      html = handlebarsRenderer.render('product.delivery_info', deliveryInfo);
      break;

    case "delivery-options":
      if (!isProductBuyable(entity)) {
        break;
      }

      const deliveryOptions = {};

      if (drupalSettings.expressDelivery.enabled) {
        // Express delivery options.
        deliveryOptions.express_delivery = {
          title: Drupal.t('Delivery Options'),
          subtitle: Drupal.t('Explore the delivery options applicable to your area.'),
          title_class: 'delivery_options',
        }
      }
      else {
        // Standard delivery options.
        deliveryOptions.home_delivery = drupalSettings.alshaya_home_delivery;
      }

      html += handlebarsRenderer.render('product.delivery_options', deliveryOptions);
      break;

    case 'mobile-upsell-products':
    case 'upsell-products':
      // Get upsell products.
      const { upsell_products } = entity || {};
      if (typeof upsell_products === 'undefined' || upsell_products.length === 0) {
        break;
      }

      html = getProductRecommendation(upsell_products, Drupal.t('You may also like', {}, { context: 'alshaya_static_text|pdp_upsell_title' }));
      break;

    case 'mobile-related-products':
    case 'related-products':
      // Get related products.
      const { related_products } = entity || {};
      if (typeof related_products === 'undefined' || related_products.length === 0) {
        break;
      }

      html = getProductRecommendation(related_products, Drupal.t('Related', {}, { context : 'alshaya_static_text|pdp_related_title' }));
      break;

    case 'mobile-crosssell-products':
    case 'crosssell-products':
      // Get related products.
      const { crosssell_products } = entity || {};
      if (typeof crosssell_products === 'undefined' || crosssell_products.length === 0) {
        break;
      }

      html = getProductRecommendation(crosssell_products, Drupal.t('Customers also bought', {}, { context: 'alshaya_static_text|pdp_crosssell_title' }));
      break;

    case 'classic-gallery':
    case 'magazine-gallery':
      let mediaCollection = {
        gallery: [],
        zoom: [],
        thumbnails: [],
      };

      if (entity.type_id === 'configurable') {
        // Fetch the media for the gallery sku.
        entity.variants.every(function (variant) {
          if (variant.product.sku !== params.skuForGallery) {
            // Continue with the loop.
            return true;
          }
          variant.product.media.forEach(function setEntityVariantThumbnails(variantMedia, i) {
            mediaCollection.thumbnails = mediaCollection.thumbnails.concat({
              index: i,
              type: 'image',
              alt: entity.name,
              title: entity.name,
              thumburl: variantMedia.thumbnails,
              mediumurl: variantMedia.medium,
              zoomurl: variantMedia.zoom,
              fullurl: variantMedia.url,
              last: (i + 1 === length) ? 'last' : '',
            });
          });
        });
      }
      else {
        entity.media.forEach(function setEntityThumbnails(entityMedia, i) {
          mediaCollection.thumbnails = mediaCollection.thumbnails.concat({
            index: i,
            type: 'image',
            alt: entity.name,
            title: entity.name,
            thumburl: entityMedia.thumbnails,
            mediumurl: entityMedia.medium,
            zoomurl: entityMedia.zoom,
            fullurl: entityMedia.url,
            last: (i + 1 === length) ? 'last' : '',
          });
        });
      }

      // If no media, return;
      if (!mediaCollection.thumbnails.length) {
        html = '';
        break;
      }

      const data = {
        description: entity.description.html,
        mainImage: {
          zoomurl: mediaCollection.thumbnails[0].zoomurl,
          mediumurl: mediaCollection.thumbnails[0].mediumurl,
          label: entity.name,
        },
        pager_flag: (mediaCollection.thumbnails.length > drupalSettings.alshayaRcs.pdpGalleryLimit[params.galleryLimit])
          ? 'pager-yes'
          : 'pager-no',
        thumbnails: mediaCollection.thumbnails,
        lazy_load_placeholder: drupalSettings.alshayaRcs.lazyLoadPlaceholder,
        pdp_gallery_type: drupalSettings.alshayaRcs.pdpGalleryType,
        skuForGallery: params.skuForGallery,
      }

      if (placeholder === 'classic-gallery') {
        html += handlebarsRenderer.render('gallery.product.product_zoom', data);
      } else {
        html += handlebarsRenderer.render('gallery.product.product_gallery_magazine', data);
      }
      break;

    case 'product-labels':
      // Remove the wrapper div if no labels are to be rendered.
      if (!Drupal.hasValue(params.labelsData)) {
        jQuery('.product-labels', params.product).remove();
        return;
      }

      const productLabelsData = {
        topRight: [],
        topLeft: [],
        bottomRight: [],
        bottomLeft: [],
        sku: params.sku,
        mainSku: params.mainSku,
        type: params.type,
      };

      params.labelsData.forEach(function (label) {
        if (!Drupal.hasValue(label.image)) {
          return;
        }

        switch (label.position) {
          case 'top-right':
            productLabelsData.topRight.push({url: label.image, name: label.name});
            break;
          case 'top-left':
            productLabelsData.topLeft.push({url: label.image, name: label.name});
            break;
          case 'bottom-right':
            productLabelsData.bottomRight.push({url: label.image, name: label.name});
            break;
          case 'bottom-left':
            productLabelsData.bottomLeft.push({url: label.image, name: label.name});
            break;
        }
      });

      const labelsMarkup = handlebarsRenderer.render('gallery.product.product_labels', {labels: productLabelsData});
      jQuery('.product-labels', params.product).html(labelsMarkup);
      break;

    default:
      Drupal.alshayaLogger('debug', 'Placeholder @placeholder not supported for render.', {
        '@placeholder': placeholder
      });
      break;
  }

  // Add class to remove loader styles after RCS info is filled.
  jQuery('.page-type-product').addClass('rcs-loaded');

  return html;
};

exports.computePhFilters = function (input, filter) {
  let value = '';
  let data = {};

  switch(filter) {
    case 'price':
      const prices = window.commerceBackend.getPrices(input, true);
      const priceVal = prices.price;
      const finalPriceVal = prices.finalPrice;

      const price = jQuery('.rcs-templates--price').clone();
      jQuery('.price-amount', price).html(priceVal);

      const priceBlock = jQuery('.rcs-templates--price_block').clone();

      if (finalPriceVal !== priceVal) {
        const finalPrice = jQuery('.rcs-templates--price').clone();
        jQuery('.price-amount', finalPrice).html(finalPriceVal);

        jQuery('.has--special--price', priceBlock).html(price.html());
        jQuery('.special--price', priceBlock).html(finalPrice.html());

        let discount = jQuery('.price--discount', priceBlock).html();
        discount = discount.replace('@discount', Math.round(input.price_range.maximum_price.discount.percent_off));
        jQuery('.price--discount', priceBlock).html(discount);
      }
      else {
        // Replace the entire price block html with this one.
        priceBlock.html(price.html());
      }

      value = jQuery(priceBlock).html();
      break;

    case 'sku':
      // @todo: Might need to make the value markup safe.
      value = input.sku;
      break;

    case 'sku-clean':
      value = Drupal.cleanCssIdentifier(input.sku);
      break;

    case 'sku-type':
      value = input.type_id;
      break;

    case 'vat_text':
      if (drupalSettings.vat_text === '' || drupalSettings.vat_text === null) {
        jQuery('.vat-text').remove();
      }
      value = drupalSettings.vat_text;
      break;

    case 'teaser_image':
      value = window.commerceBackend.getTeaserImage(input);
      break;

    case 'add_to_cart':
      value = '';

      if (!isProductBuyable(input)) {
        // Just show the not buyable text and don't show the form.
        data = {
          not_buyable_message: drupalSettings.alshayaRcs.not_buyable_message,
          not_buyable_help_text: drupalSettings.alshayaRcs.not_buyable_help_text,
        };
        value = handlebarsRenderer.render(`product.not_buyable_product`, data);
        break;
      }

      data.sku = input.sku;
      data.sku_clean = Drupal.cleanCssIdentifier(input.sku);
      data.add_to_cart_text = Drupal.t('add to cart');
      data.sku_type = input.type_id;

      if (drupalSettings.alshayaRcs.showQuantity) {
        const quantity = parseInt(drupalSettings.alshaya_spc.cart_config.max_cart_qty, 10);
        const quantityValues = [];
        for (let i = 1; i <= quantity; i++) {
          quantityValues.push(i);
        }
        data.quantity_dropdown = quantityValues;
      }

      let configurableOptions = input.configurable_options;

      if (typeof configurableOptions !== 'undefined' && configurableOptions.length > 0) {
        const hiddenFormAttributes = (typeof drupalSettings.hidden_form_attributes !== 'undefined')
          ? drupalSettings.hidden_form_attributes
          : [];

        const availableOptions = disableUnavailableOptions(input.sku, configurableOptions);
        let processedOptions = [];

        availableOptions.forEach((option) => {
          // Get the value to be used in HTML attributes.
          const formattedAttributeCode = option.attribute_code.replaceAll('_', '-');
          // Check if the attribute is a swatch attribute.
          const isOptionSwatch = drupalSettings.alshayaRcs.pdpSwatchAttributes.includes(option.attribute_code);
          let dataDefaultTitle = option.label;
          let dataTitle = null;
          const configurableColorDetails = window.commerceBackend.getConfigurableColorDetails(input.sku);

          if (Drupal.hasValue(configurableColorDetails) && isOptionSwatch){
            dataDefaultTitle = Drupal.t('Color');
            dataTitle = Drupal.t('Color');
          }

          const selectOptions = [];
          // Add the option values.
          option.values.forEach((value) => {
            const label = window.commerceBackend.getAttributeValueLabel(option.attribute_code, value.value_index);
            let selectOption = { value: value.value_index, text: label };

            if (isOptionSwatch) {
              const childSku = getChildSkuFromAttribute(input.sku, option.attribute_code, value.value_index);
              // If configurableColorDetails has value, then we process the
              // swatch data in
              // Drupal.alshaya_color_images_generate_swatch_markup().
              if (childSku !== null && !Drupal.hasValue(configurableColorDetails)) {
                selectOption['swatch-image'] = getPdpSwatchImageUrl(input, childSku);
              }
            }
            selectOptions.push(selectOption);
          });

          const configurableOption = ({
            data_configurable_code: option.attribute_code,
            data_default_title: dataDefaultTitle,
            default_title: dataTitle,
            data_selected_title: option.label,
            data_drupal_selector: `edit-configurables-${formattedAttributeCode}`,
            id: `edit-configurables-${formattedAttributeCode}`,
            class: isOptionSwatch ? 'form-item-configurable-swatch' : 'form-item-configurable-select',
            wrapperClass: isOptionSwatch ? 'configurable-swatch' : 'configurable-select',
            name: `configurables[${option.attribute_code}]`,
            aria_require: true,
            aria_invalid: false,
            select_option_label: Drupal.t(`Select @attr`, { '@attr': option.attribute_code }),
            select_options: selectOptions,
            hidden: hiddenFormAttributes.includes(option.attribute_code),
            attribute_has_size_guide: drupalSettings.alshayaRcs.sizeGuide.attributes.includes(option.attribute_code),
          });

          processedOptions.push(configurableOption);
        });
        data.size_guide_link = drupalSettings.alshayaRcs.sizeGuide.link;
        // Add the configurable options to the form.
        data.configurable_options = processedOptions;
      }
      data.vmode = drupalSettings.alshayaRcs.vmode;
      value = handlebarsRenderer.render(`product.sku_base_form`, data);
      break;

    case 'gtm-price':
      value = input.price_range.maximum_price.regular_price.value;
      break;

    case 'final_price':
      value = input.price_range.maximum_price.regular_price.value;
      break;

    case 'first_image':
      const firstImage = window.commerceBackend.getFirstImage(input);
      value = Drupal.hasValue(firstImage)
        ? firstImage.url
        : drupalSettings.alshayaRcs.default_meta_image;
      break;

    case 'schema_stock':
      if (input.stock_status === 'IN_STOCK') {
        value = 'http://schema.org/InStock';
      }
      else {
        value = 'http://schema.org/OutOfStock';
      }
      break;

    case 'url':
      value = `${input.url_key}.html`;
      break;

    case 'brand_logo':
      if (input.brand_logo_data.url !== null) {
        data = {
          url : input.brand_logo_data.url,
          alt : input.brand_logo_data.alt,
          title : input.brand_logo_data.title,
        };
      }
      value = handlebarsRenderer.render(`attribute.brand.logo`, data);

      break;

    case 'name':
      value = input.name;
      break;

    case 'description':
      // Prepare the object data for rendering.
      data = input.description;

      // Add legal notice.
      data.legal_notice = getLegalNotice();

      // Add Additional description.
      data.additional_description = getAdditionalPdpDescription();

      // Render handlebars plugin.
      value = handlebarsRenderer.render(`product.block.${filter}`, data);
      break;

    case 'short_description':
      // Prepare the object data for rendering.
      data = (input.short_description) ? input.short_description : input.description;

      // Add description (Used inside short description on Magazine layout).
      data.description = input.description;
      // Add legal notice.
      data.description.legal_notice = getLegalNotice();

      // Add Additional description.
      data.description.additional_description = getAdditionalPdpDescription();

      // Apply ellipsis.
      let tmp = applyEllipsis(data.html);
      data.value = tmp.value;
      data.read_more = tmp.read_more;

      // Render handlebars plugin.
      value = handlebarsRenderer.render(`product.block.${filter}.${drupalSettings.alshayaRcs.pdpLayout}`, data);
      break;

    case 'promotions':
      const promotions = input.promotions || [];
      if (typeof promotions === 'undefined' || promotions === null) {
        break;
      }
      data.sku = input.sku;

      let promotionsList = [];
      promotions.forEach((promotion, index) => {
        const context = promotion.context || {};
        if (typeof context === 'undefined' || !context.includes('web')) {
          return;
        }
        // Prepare the data object.
        promotionsList.push({
          link: Drupal.url(promotion.promo_web_url),
          hreflang: drupalSettings.path.currentLanguage,
          label: promotion.text,
        });
      });
      data.promotions = promotionsList;

      // Render handlebars plugin.
      value = handlebarsRenderer.render(`product.${filter}`, data);
      break;

    case 'price_block_identifier':
      const cleanSku = Drupal.cleanCssIdentifier(input.sku);
      value = `price-block-${cleanSku}`;
      break;

    default:
      Drupal.alshayaLogger('debug', 'Unknown JS filter @filter.', {'@filter': filter})
  }

  return value;
};
