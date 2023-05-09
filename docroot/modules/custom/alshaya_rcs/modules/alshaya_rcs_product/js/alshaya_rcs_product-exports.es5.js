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
 * Get data for product recommendations.
 *
 * @param {object} products
 *   Products to be displayed.
 * @param {string} sectionTitle
 *   The translated title for related, upsell.
 *
 * @returns {object}
 *   Data to use for rendering product recommendations.
 */
function getProductRecommendation(products, sectionTitle) {
  const data = {
    products: [],
    subtitle: sectionTitle,
  };

  products.forEach((product) => {
    data.products.push({
      sku: product.sku,
      url: Drupal.url(`${product.url_key}.html`),
      name: product.name,
      image: window.commerceBackend.getTeaserImage(product),
      price_details: window.commerceBackend.getPriceForRender(product),
      cleanSku: Drupal.cleanCssIdentifier(product.sku),
      gtm: product.gtm_attributes,
    });
  });

  return data;
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
      if (!Drupal.hasValue(swatchImageUrl) && Drupal.hasValue(variant.product.swatch_image)) {
        swatchImageUrl = variant.product.swatch_image_url || null;
      }
      // Break from the loop.
      return false;
    }
  });

  return swatchImageUrl;
}

/**
 * Get the size group data for the provide sku.
 *
 * @param {string} sku
 *   The SKU value.
 *
 * @returns {string|null}
 *   The size group.
 */
 function getPdpSizeGroupData(product, childSku) {
  let sizeGroup = {};
  let flag = true;
  const sizeGroupAlternates = drupalSettings.alshayaRcs.pdpSizeGroupAlternates;
  product.variants.forEach(function (variant) {
    if (variant.product.sku == childSku) {
      sizeGroupAlternates.forEach(function (alternates) {
        const value = variant.product[alternates.value];
        if (!Drupal.hasValue(value)) {
          flag = false;
        }
        const valueLabel = window.commerceBackend.getAttributeValueLabel(alternates.value, value);
        sizeGroup[alternates.value] = {
          'label': alternates.label,
          'value': valueLabel,
        }
      });
      // Break from the loop.
      return false;
    }
  });

  return (flag) ? JSON.stringify(sizeGroup) : null;
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

  // Clone this to not modify the original object.
  let configurableOptionsClone = JSON.parse(JSON.stringify(configurableOptions));

  configurableOptionsClone.forEach(function eachOption(option) {
    option.values = option.values.filter(function eachValue(value) {
      if (Drupal.hasValue(combinations.attribute_sku[option.attribute_code][value.value_index])) {
        return true;
      }
    });
  });

  return configurableOptionsClone;
}

/**
 * Processes media into Media collection object.
 *
 * @param {object} media
 *   Product media.
 * @param {object} entity
 *   Product entity.
 * @param {string} entity
 *   Product entity.
 * @param {string} length
 *   Product entity.
 *
 * @returns {object}
 *   Returns media collection object.
 */
function setMediaCollection(media, entity, index, length) {
  if (!Drupal.hasValue(media.type) || media.type === 'image') {
    return {
      index: index,
      type: 'image',
      alt: entity.name,
      title: entity.name,
      thumburl: media.thumbnails,
      mediumurl: media.medium,
      zoomurl: media.zoom,
      fullurl: media.url,
      last: (index + 1 === length) ? 'last' : '',
    };
  }
  else {
    return {
      index: index,
      type: 'video',
      alt: entity.name,
      title: entity.name,
      fullurl: media.url,
      last: (index + 1 === length) ? 'last' : '',
    };
  }
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

      const upsellProducts = getProductRecommendation(upsell_products, Drupal.t('You may also like', {}, { context: 'alshaya_static_text|pdp_upsell_title' }));
      html += handlebarsRenderer.render('product.recommended_products_block', upsellProducts);
      break;

    case 'mobile-related-products':
    case 'related-products':
      // Get related products.
      const { related_products } = entity || {};
      if (typeof related_products === 'undefined' || related_products.length === 0) {
        break;
      }

      const relatedProducts = getProductRecommendation(related_products, Drupal.t('Related', {}, { context : 'alshaya_static_text|pdp_related_title' }));
      html += handlebarsRenderer.render('product.recommended_products_block', relatedProducts);
      break;

    case 'mobile-crosssell-products':
    case 'crosssell-products':
      // Get related products.
      const { crosssell_products } = entity || {};
      if (typeof crosssell_products === 'undefined' || crosssell_products.length === 0) {
        break;
      }

      const crossselProducts = getProductRecommendation(crosssell_products, Drupal.t('Customers also bought', {}, { context: 'alshaya_static_text|pdp_crosssell_title' }));
      html += handlebarsRenderer.render('product.recommended_products_block', crossselProducts);
      break;

    case 'classic-gallery':
    case 'magazine-gallery':
      let mediaCollection = {
        gallery: [],
        zoom: [],
        thumbnails: [],
      };
      let weight = '';
      let article_number = '';
      if (entity.type_id === 'configurable') {
        // Fetch the media for the gallery sku.
        entity.variants.every(function (variant) {
          if (variant.product.sku !== params.skuForGallery) {
            // Continue with the loop.
            return true;
          }
          weight = (Drupal.hasValue(variant.product.weight)) ? variant.product.weight_text : '';
          article_number = (Drupal.hasValue(variant.product.article_number)) ? variant.product.article_number : '';
          variant.product.media.forEach(function setEntityVariantThumbnails(variantMedia, i) {
            mediaCollection.thumbnails = mediaCollection.thumbnails.concat(setMediaCollection(variantMedia, entity, i, length));
          });
        });
      }
      else {
        entity.media.forEach(function setEntityThumbnails(entityMedia, i) {
          mediaCollection.thumbnails = mediaCollection.thumbnails.concat(setMediaCollection(entityMedia, entity));
        });
      }

      // If no media, return;
      if (!mediaCollection.thumbnails.length) {
        html = '';
        break;
      }
      entity.description.article_number = article_number;
      entity.description.weight = weight;

      const data = {
        description: entity.description,
        context: entity.context,
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

    case 'old-price':
      const priceInfo = window.commerceBackend.getPrices(input, true);
      // See Drupal\alshaya_seo_transac\AlshayaGtmManager::fetchSkuAtttributes().
      if (Drupal.hasValue(priceInfo)
        && ((priceInfo.price != priceInfo.finalPrice)
        && (priceInfo.finalPrice < priceInfo.price))) {
        value = priceInfo.price;
      }
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

      if (!window.commerceBackend.isProductInStock(input)) {
        value = handlebarsRenderer.render(`product.sku_base_form_oos`, {text: Drupal.t('Out of stock')});
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
        data.quantity_title = Drupal.t('Quantity');
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
          // Check if the attribute is a size group attribute.
          let isSizeGroupOption = false;
          if (Drupal.hasValue(drupalSettings.alshayaRcs.pdpSizeGroupAttribute)) {
            isSizeGroupOption = drupalSettings.alshayaRcs.pdpSizeGroupAttribute.includes(option.attribute_code);
          }

          let dataDefaultTitle = option.label;
          let dataTitle = null;
          const configurableColorDetails = window.commerceBackend.getConfigurableColorDetails(input.sku);

          if (Drupal.hasValue(configurableColorDetails) && isOptionSwatch){
            dataDefaultTitle = Drupal.t('Color');
            dataTitle = Drupal.t('Color');
          }

          const selectOptions = [];
          let sizeGroupData = null;
          // Add the option values.
          option.values.forEach((value) => {
            let label = window.commerceBackend.getAttributeValueLabel(option.attribute_code, value.value_index);
            // Use the index value as label when the label doesn't exists for
            // respective index.
            if (!Drupal.hasValue(label)) {
              label = value.value_index;
              // Add a logger here so that we have info around the content where
              // we don't have label.
              Drupal.alshayaLogger('debug', '@attribute label is missing for the index @value_index .', {
                '@attribute': option.attribute_code,
                '@value_index': label,
              });
            }
            let selectOption = { value: value.value_index, text: label };

            if (isOptionSwatch) {
              const childSku = window.commerceBackend.getChildSkuFromAttribute(input.sku, option.attribute_code, value.value_index);
              // If configurableColorDetails has value, then we process the
              // swatch data in
              // Drupal.alshaya_color_images_generate_swatch_markup().
              if (childSku !== null && !Drupal.hasValue(configurableColorDetails)) {
                selectOption['swatch-image'] = getPdpSwatchImageUrl(input, childSku);
              }
            } else if (isSizeGroupOption) {
              const childSku = window.commerceBackend.getChildSkuFromAttribute(input.sku, option.attribute_code, value.value_index);
              sizeGroupData = getPdpSizeGroupData(input, childSku);
              // If configurableSizeGroup has value, then we process the group data.
              if (childSku !== null && Drupal.hasValue(sizeGroupData)) {
                selectOption['group-data'] = sizeGroupData;
              }
            }
            selectOptions.push(selectOption);
          });

          /**
           * Returns the error message for labels.
           *
           * @param string attribute
           *   The attribute.
           *
           * @param string label
           *   The field label.
           *
           * @return string
           *   The error message.
           */
          const getLabelErrorMessage = (attribute, label) => {
            const messages = drupalSettings.alshayaRcs.fieldLabelErrorMessages;
            const lang = drupalSettings.path.currentLanguage;
            if (Drupal.hasValue(messages)
              && Drupal.hasValue(messages[attribute])
              && Drupal.hasValue(messages[attribute][lang])
              && Drupal.hasValue(messages[attribute][lang].error)
            ) {
              return messages[attribute][lang].error;
            }

            return Drupal.t('@title field is required.', { '@title': label });
          };

          let configurableClass = 'form-item-configurable-select';
          let configurableWrapperClass = 'configurable-select';
          if (isOptionSwatch) {
            configurableClass = 'form-item-configurable-swatch';
            configurableWrapperClass = 'configurable-swatch';
          } else if (isSizeGroupOption && Drupal.hasValue(sizeGroupData)) {
            configurableClass = 'form-item-configurable-select-group';
          }

          const configurableOption = ({
            data_configurable_code: option.attribute_code,
            data_default_title: dataDefaultTitle,
            default_title: dataTitle,
            data_selected_title: option.label,
            data_drupal_selector: `edit-configurables-${formattedAttributeCode}`,
            id: `edit-configurables-${formattedAttributeCode}`,
            class: configurableClass,
            wrapperClass: configurableWrapperClass,
            hiddenClass: (hiddenFormAttributes.includes(option.attribute_code)) ? 'hidden' : '',
            name: `configurables[${option.attribute_code}]`,
            data_msg_required: getLabelErrorMessage(option.attribute_code, dataDefaultTitle),
            required: 'required',
            aria_require: true,
            aria_invalid: false,
            select_option_label: Drupal.t(`Select @title`, { '@title': option.label }),
            select_options: selectOptions,
            attribute_has_size_guide: drupalSettings.alshayaRcs.sizeGuide.attributes.includes(option.attribute_code),
          });

          processedOptions.push(configurableOption);
        });
        data.size_guide_link = drupalSettings.alshayaRcs.sizeGuide.link;
        // Add the configurable options to the form.
        data.configurable_options = processedOptions;
      }
      // Free gift related details for rendering add to cart form
      // for free gifts inside modal.
      if (input.context === 'free_gift') {
        data.couponCode = input.couponCode;
        data.promoRuleId = input.promoRuleId;
        value = handlebarsRenderer.render(`product.free_gift_sku_base_form`, data);
      }
      else {
        value = handlebarsRenderer.render(`product.sku_base_form`, data);
      }
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

    case 'url_encode':
      value = Drupal.encodePath(input);
      break;

    case 'absolute_url':
      value = Drupal.url.toAbsolute(`${input.url_key}.html`);
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
      // Render handlebars plugin.
      value = handlebarsRenderer.render(`product.block.${filter}`, input);
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
