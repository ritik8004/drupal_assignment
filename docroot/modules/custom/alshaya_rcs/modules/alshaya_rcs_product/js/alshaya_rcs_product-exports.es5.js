/**
 * Check if product is available for home delivery.
 *
 * @param {object} entity
 *   The product entity.
 *
 * @returns {Boolean}
 *   Returns true if the product is available for home delivery else false.
 *
 * @see alshaya_acm_product_available_home_delivery().
 */
function isProductAvailableForHomeDelivery(entity) {
  return isProductBuyable(entity);
}

/**
 * Check if the provided product is available for Click and Collect.
 *
 * @param entity
 *   The entity.
 *
 * @return {Boolean}
 *   True if CNC is available, otherwise false.
 */
function isProductAvailableForClickAndCollect(entity) {
  return window.commerceBackend.isProductAvailableForClickAndCollect(entity);
}

/**
 * Check if the provided product is available for Same day delivery.
 *
 * @param entity
 *   The entity.
 *
 * @return {Boolean}
 *   True if SDD is available, otherwise false.
 */
function isProductAvailableForSameDayDelivery(entity) {
  return entity.same_day_delivery === 1;
}

/**
 * Check if the provided product is available for Express delivery.
 *
 * @param entity
 *   The entity.
 *
 * @return {Boolean}
 *   True if ED is available, otherwise false.
 */
function isProductAvailableForExpressDelivery(entity) {
  return entity.express_delivery === 1;
}

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
    const attributes = rcsPhGetSetting('placeholderAttributes');
    finalMarkup = related.html();
    rcsPhReplaceEntityPh(finalMarkup, 'product_teaser', product, drupalSettings.path.currentLanguage)
      .forEach(function eachReplacement(r) {
        const fieldPh = r[0];
        const entityFieldValue = r[1];
        finalMarkup = rcsReplaceAll(finalMarkup, fieldPh, entityFieldValue);
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
    Drupal.alshayaLogger('debug', 'No combination available for attribute @attribute and option @option_id for SKU @sku', {
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
        if (option.status === 1 && entity[option.id] === 1) {
          deliveryInfo.expressDelivery.push(option);
        }
      });

      // Same day delivery.
      if (entity.same_day_delivery === 1) {
        deliveryInfo.sameDayDelivery = drupalSettings.alshayaRcs.pdp.sameDayDelivery;
      }

      html = handlebarsRenderer.render('product.delivery_info', deliveryInfo);
      break;

    case "delivery-options":
      if (!isProductBuyable(entity)) {
        break;
      }

      const deliveryOptions = {};
      const cncEnabled = isProductAvailableForClickAndCollect(entity);
      if (cncEnabled) {
        deliveryOptions.cnc = {
          state: cncEnabled ? 'enabled' : 'disabled',
            title: drupalSettings.alshaya_click_collect.title,
            subtitle: (cncEnabled === true)
            ? drupalSettings.alshaya_click_collect.subtitle.enabled
            : drupalSettings.alshaya_click_collect.subtitle.disabled,
            sku: entity.sku,
            sku_clean: window.commerceBackend.cleanCssIdentifier(entity.sku),
            sku_type: entity.type_id,
            help_text: drupalSettings.alshaya_click_collect.help_text,
            available_at_title: '',
            select_option_text: drupalSettings.alshaya_click_collect.select_option_text,
            store_finder_form: drupalSettings.alshaya_click_collect.store_finder_form,
        };
      }

      const hdEnabled = isProductAvailableForHomeDelivery(entity);
      if (hdEnabled) {
        const skuSddEnabled = isProductAvailableForSameDayDelivery(entity);
        const skuEdEnabled = isProductAvailableForExpressDelivery(entity);
        if (drupalSettings.expressDelivery.enabled && (skuSddEnabled || skuEdEnabled)) {
          // Express delivery options.
          deliveryOptions.hd = {
            type: 'express_delivery',
            title: Drupal.t('Delivery Options'),
            subtitle: Drupal.t('Explore the delivery options applicable to your area.'),
          }
        }
        else {
          // Standard delivery options.
          deliveryOptions.hd = drupalSettings.alshaya_home_delivery;
          deliveryOptions.hd.type = 'standard_delivery';
        }
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
      let mediaCollection = {
        gallery: [],
        zoom: [],
        thumbnails: [],
      };

      switch (drupalSettings.alshayaRcs.useParentImages) {
        case 'never':
          // Get the images from the variants.
          entity.variants.forEach(function (variant) {
            // Only fetch media for the selected variant.
            if (variant.product.sku !== params.sku) {
              return;
            }
            variant.product.media.forEach(function (variantMedia) {
              mediaCollection.thumbnails = mediaCollection.thumbnails.concat({
                type: 'image',
                thumburl: variantMedia.thumbnails,
                mediumurl: variantMedia.medium,
                zoomurl: variantMedia.zoom,
                fullurl: variantMedia.url,
              });
            });
            // Break from the loop.
            return false;
          });
          break;

        default:
          // @todo Add default case when working on other brands.
          break;
      }

      // If no media, return;
      if (!mediaCollection.thumbnails.length) {
        html = '';
        break;
      }

      const data = {
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
      }

      html += handlebarsRenderer.render('gallery.product.product_zoom', data);
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
        value = window.commerceBackend.cleanCssIdentifier(input.sku);
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
        value = jQuery('.rcs-templates--not-buyable-product').html();
        break;
      }

      const skuBaseForm = jQuery('.rcs-templates--sku-base-form').clone();
      skuBaseForm.find('.sku-base-form-template').removeClass('sku-base-form-template').addClass('sku-base-form');

      if (drupalSettings.alshayaRcs.showQuantity) {
        // @todo Check for how to fetch the max sale quantity.
        const quantity = parseInt(drupalSettings.alshaya_spc.cart_config.max_cart_qty, 10);
        const quantityDroprown = jQuery('.edit-quantity', skuBaseForm);
        // Remove the quantity filter.
        quantityDroprown.html('');

        for (let i = 1; i <= quantity; i++) {
          if (i === 1) {
            quantityDroprown.append('<option value="' + i + '" selected="selected">' + i + '</option>');
            continue;
          }
          quantityDroprown.append('<option value="' + i + '">' + i + '</option>');
        }
        jQuery('.js-form-item-quantity', skuBaseForm).children(quantityDroprown);
      }
      else {
        jQuery('.js-form-item-quantity', skuBaseForm).remove();
      }


      // This wrapper will be removed after processing.
      const tempDivWrapper = jQuery('<div>');
      let configurableOptions = input.configurable_options;

      if (typeof configurableOptions !== 'undefined' && configurableOptions.length > 0) {
        const sizeGuide = jQuery('.rcs-templates--size-guide');
        let sizeGuideAttributes = [];
        if (sizeGuide.length) {
          sizeGuideAttributes = sizeGuide.attr('data-attributes');
          sizeGuideAttributes = sizeGuideAttributes ? sizeGuideAttributes.split(',') : sizeGuideAttributes;
        }

        const hiddenFormAttributes = (typeof drupalSettings.hidden_form_attributes !== 'undefined')
          ? drupalSettings.hidden_form_attributes
          : [];

        configurableOptions.forEach((option) => {
          // Get the field wrapper div.
          const optionsListWrapper = jQuery('.rcs-templates--form_element_select').clone().children();
          // The list containing the options.
          const configurableOptionsList = jQuery('<select></select>');
          // Get the value to be used in HTML attributes.
          const formattedAttributeCode = option.attribute_code.replaceAll('_', '-');

          configurableOptionsList.attr({
            'data-configurable-code': option.attribute_code,
            // @todo: Find out the correct label for color.
            'data-default-title': option.label,
            // @todo: Find out the correct label for color.
            'data-selected-title': option.label,
            'data-drupal-selector': `edit-configurables-${formattedAttributeCode}`,
            id: `edit-configurables-${formattedAttributeCode}`,
            class: 'form-select required valid',
            name: `configurables[${option.attribute_code}]`,
            'aria-require': true,
            'aria-invalid': false,
          });

          // Check if the attribute is a swatch attribute.
          let optionIsSwatch = false;
          if (drupalSettings.alshayaRcs.pdpSwatchAttributes.includes(option.attribute_code)) {
            optionIsSwatch = true;
            configurableOptionsList.addClass('form-item-configurable-swatch');
            optionsListWrapper.addClass('configurable-swatch');
          }
          else {
            configurableOptionsList.addClass('form-item-configurable-select');
            optionsListWrapper.addClass('configurable-select');
          }

          // Add a disabled option which will be used as the label for the option.
          let selectOption = jQuery('<option></option>');
          let text = Drupal.t(`Select @attr`, { '@attr': option.attribute_code });
          selectOption.attr({selected: 'selected', disabled: 'disabled'}).text(text);
          configurableOptionsList.append(selectOption);

          const configurableColorDetails = window.commerceBackend.getConfigurableColorDetails(input.sku);

          if (Drupal.hasValue(configurableColorDetails) && optionIsSwatch){
            configurableOptionsList.attr({
              'data-default-title': Drupal.t('Color'),
              'title': Drupal.t('Color'),
            });
          }

          // Add the option values.
          option.values.forEach((value) => {
            selectOption = jQuery('<option></option>');
            const label = window.commerceBackend.getAttributeValueLabel(option.attribute_code, value.value_index);
            selectOption.attr({value: value.value_index}).text(label);
            configurableOptionsList.append(selectOption);

            if (optionIsSwatch) {
              const childSku = getChildSkuFromAttribute(input.sku, option.attribute_code, value.value_index);
              // If configurableColorDetails has value, then we process the
              // swatch data in
              // Drupal.alshaya_color_images_generate_swatch_markup().
              if (childSku !== null && !Drupal.hasValue(configurableColorDetails)) {
                selectOption.attr({'swatch-image': getPdpSwatchImageUrl(input, childSku)});
              }
            }
          });

          if (sizeGuideAttributes.includes(option.attribute_code)) {
            const sizeGuideLink = sizeGuide.children();
            const sizeGuideWrapper = jQuery('<div>');
            sizeGuideWrapper.addClass('size-guide-form-and-link-wrapper');
            sizeGuideWrapper.append(sizeGuideLink);
            sizeGuideWrapper.append(optionsListWrapper);
            // Append to the main wrapper.
            tempDivWrapper.append(sizeGuideWrapper);
          }
          else {
            // Append to the main wrapper.
            tempDivWrapper.append(optionsListWrapper);
          }

          optionsListWrapper.append(configurableOptionsList);
          // Replace the placeholder class name.
          optionsListWrapper.attr('class', optionsListWrapper[0].className.replaceAll('ATTRIBUTENAME', formattedAttributeCode));
          // Hide field if supposed to be hidden.
          if (hiddenFormAttributes.includes(option.attribute_code)) {
            optionsListWrapper.addClass('hidden');
          }
        });

        // Add the configurable options to the form.
        jQuery('#configurable_ajax', skuBaseForm).append(tempDivWrapper.children());
      }

      let finalHtml = skuBaseForm.html();
      rcsPhReplaceEntityPh(finalHtml, 'product_add_to_cart', input, drupalSettings.path.currentLanguage)
        .forEach(function eachReplacement(r) {
          const fieldPh = r[0];
          const entityFieldValue = r[1];
          finalHtml = rcsReplaceAll(finalHtml, fieldPh, entityFieldValue);
        });

      value = finalHtml;
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
        const image = jQuery('img');
        image.attr({
          src: input.brand_logo_data.url,
          alt: input.brand_logo_data.alt,
          title: input.brand_logo_data.title,
        });
        value = jQuery('.rcs-templates--brand_logo').clone().append(image).html();
      }

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

    default:
      Drupal.alshayaLogger('debug', 'Unknown JS filter @filter.', {'@filter': filter})
  }

  return value;
};
