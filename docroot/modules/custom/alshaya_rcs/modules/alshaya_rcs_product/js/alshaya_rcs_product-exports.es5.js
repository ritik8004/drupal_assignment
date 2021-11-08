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
    console.log(`No combination available for attribute ${attribute} and option ${option_id} for SKU ${sku}`);
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
    case "delivery-option":
      if (!isProductBuyable(entity)) {
        break;
      }

      const deliveryOptionsWrapper = jQuery('.rcs-templates--delivery_option').clone();
      const cncEnabled = window.commerceBackend.isProductAvailableForClickAndCollect(entity);
      const isDeliveryOptionsAvailable = isProductAvailableForHomeDelivery(entity) || cncEnabled;

      if (isDeliveryOptionsAvailable) {
        const homeDelivery = jQuery('.rcs-templates--delivery_option-home-delivery').clone().children();
        jQuery('.field__content', deliveryOptionsWrapper).append(homeDelivery);

        const clickAndCollect = jQuery('.rcs-templates--delivery_option-click-and-collect').clone().children();

        if (entity.type_id === 'configurable') {
          jQuery('.c-accordion_content .simple', clickAndCollect).remove();
        }
        else {
          jQuery('.c-accordion_content .configurable', clickAndCollect).remove();
        }

        clickAndCollect.attr({
          'data-state': cncEnabled ? 'enabled' : 'disabled',
          'data-product-type': entity.type_id,
          'data-sku-clean': window.commerceBackend.cleanCssIdentifier(entity.sku),
        });

        const subTitle = cncEnabled
          ? drupalSettings.alshaya_click_collect.subtitle.enabled
          : drupalSettings.alshaya_click_collect.subtitle.disabled;
        jQuery('.subtitle', clickAndCollect).html(subTitle);

        // Add click and collect to the delivery options field.
        jQuery('.field__content', deliveryOptionsWrapper).append(clickAndCollect);
      }

      html += deliveryOptionsWrapper.html();
      break;

    case 'product_category_list':
      // Process rcs plp renderer, if available.
      if (typeof globalThis.renderRcsListing !== 'undefined') {
        html += globalThis.renderRcsListing.render(
          entity,
          innerHtml
        );
      }
      break;

    case 'mobile-upsell-products':
    case 'upsell-products':
      // Get upsell products.
      const { upsell_products } = entity || {};
      if (typeof upsell_products === 'undefined' || upsell_products.length === 0) {
        break;
      }

      html = getProductRecommendation(upsell_products, rcsTranslatedText('You may also like', {}, 'alshaya_static_text|pdp_upsell_title'));
      break;

    case 'mobile-related-products':
    case 'related-products':
      // Get related products.
      const { related_products } = entity || {};
      if (typeof related_products === 'undefined' || related_products.length === 0) {
        break;
      }

      html = getProductRecommendation(related_products, rcsTranslatedText('Related', {}, 'alshaya_static_text|pdp_related_title'));
      break;

    case 'mobile-crosssell-products':
    case 'crosssell-products':
      // Get related products.
      const { crosssell_products } = entity || {};
      if (typeof crosssell_products === 'undefined' || crosssell_products.length === 0) {
        break;
      }

      html = getProductRecommendation(crosssell_products, rcsTranslatedText('Customers also bought', {}, 'alshaya_static_text|pdp_crosssell_title'));
      break;

    case 'classic-gallery':
      const gallery = jQuery('.rcs-templates--rcs-product-zoom');
      const mediaCollection = entity.media_gallery;

      // If no media, return;
      if (!mediaCollection.length) {
        html = '';
        break;
      }

      mediaCollection.forEach((media) => {
        const galleryElement = jQuery('<li></li>');

        const galleryElementAnchor = jQuery('<a></a>');
        galleryElementAnchor.attr({
          href: media.url,
          class: 'imagegallery__thumbnails__image a-gallery',
        });

        const galleryElementImg = jQuery('<img />');
        galleryElementImg.attr({
          class: 'b-lazy',
          // @todo: Replace with lazy loaded image.
          src: media.url,
          'data-src': media.url,
          alt: media.label,
          title: media.label,
        });

        galleryElementAnchor.append(galleryElementImg);
        galleryElement.append(galleryElementAnchor);
        jQuery('#product-full-screen-gallery', gallery).append(galleryElement);
      });

      // Get the template for the thumbnails.
      const thumbnailTemplate = jQuery('.rcs-templates--product_thumbnails').clone();

      // This is the list that will hold the thumbnails.
      const thumbnails = jQuery('<ul id="lightSlider"></ul>');

      mediaCollection.forEach((media) => {
        // @todo: Fetch the type from the input.
        const type = 'image';
        switch (type) {
          case 'youtube':
            break;

          case 'vimeo':
            break;

          case 'pdp-video':
            break;

          // Image.
          default:
            const imageUrl = media.url;
            const imageLabel = media.label;
            // Get the image element from the template and start adding the
            // required attributes.
            const element = jQuery('.default', thumbnailTemplate).clone();
            const anchor = jQuery('a', element);

            anchor.attr({
              // @todo: Replace this with the zoomed image.
              'data-zoom-url': imageUrl,
              // @todo: Replace this with the medium image.
              href: imageUrl,
            });

            jQuery('img', anchor).attr({
              src:  imageUrl,
              'data-src': imageUrl,
              alt: imageLabel,
              title: imageLabel,
            });

            // Append the li element to the list.
            thumbnails.append(element);
        }
      });

      if (mediaCollection.length > drupalSettings.alshayaRcs.pdpGalleryPagerLimit) {
        thumbnails.addClass('pager-yes');
      }
      else {
        thumbnails.addClass('pager-no');
      }

      jQuery('.cloudzoom__thumbnails', gallery).html(thumbnails);

      // Get the template for the mobile.
      const mobileGalleryTemplate = jQuery('.rcs-templates--product_gallery_mobile').clone();
      const mobileGallery = jQuery('<div>');

      mediaCollection.forEach((media) => {
        // @todo: Fetch the type from the input.
        const type = 'image';
        switch (type) {
          case 'youtube':
            break;

          case 'vimeo':
            break;

          case 'pdp-video':
            break;

          // Image.
          default:
            const imageUrl = media.url;
            const imageLabel = media.label;
            // Get the image element from the template and start adding the
            // required attributes.
            const element = jQuery('.default', mobileGalleryTemplate).clone();

            jQuery('img', element).attr({
              src:  imageUrl,
              'data-src': imageUrl,
              alt: imageLabel,
              title: imageLabel,
            });

            // Append the li element to the list.
            mobileGallery.append(element);
        }
      });

      jQuery('#product-image-gallery-mobile', gallery).html(mobileGallery.html());

      // Get the labels data.
      const labels = inputs.labels;
      if (labels.length) {
        const labelsData = {};
        labels.forEach(function (label) {
          // Don't render labels with unknown positions.
          if (!(['top-left', 'top-right', 'bottom-left', 'bottom-right'].includes(label.position))) {
            return;
          }

          if (typeof labelsData[label.position] === 'undefined') {
            labelsData[label.position] = [];
          }
          // Group the labels by position.
          labelsData[label.position].push(label);
        });

        const productLabels = jQuery('<div class="product-labels"><div class="labels-wrapper" data-type="pdp" data-sku="#rcs.product._self|sku#" data-main-sku="#rcs.product._self|sku#"></div></div>');
        const labelsWrapper = productLabels.find('.labels-wrapper');

        Object.keys(labelsData).forEach(function (position) {
          const positionLabels = jQuery('<div>').addClass(`labels-container ${position}`);
          labelsData[position].forEach(function (labelData) {
            const individualLabel = jQuery('<div>').addClass('label');
            const img = jQuery('<img>').attr({
              src: labelData.image,
              alt: labelData.name,
              title: labelData.name,
            });
            individualLabel.append(img);
            positionLabels.append(individualLabel);
          });
          labelsWrapper.append(positionLabels);
        });

        // jQuery('.product-labels .labels-wrapper', gallery).html(productLabels.html());
        const productLabelsMarkup = productLabels.html();
        jQuery('.cloudzoom__herocontainer', gallery).append(productLabelsMarkup);
        jQuery('.mobilegallery', gallery).append(productLabelsMarkup);
      }

      html += gallery.html();
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for render.`);
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
      const priceVal = getFormattedAmount(input.price_range.maximum_price.regular_price.value);
      const finalPriceVal = getFormattedAmount(input.price_range.maximum_price.final_price.value);

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

    case 'image':
      value = ((input.media_gallery.length > 0)
          && (typeof input.media_gallery[0].url !== 'undefined'
            || input.media_gallery[0].url
            || input.media_gallery[0].url !== '')
        )
        ? input.media_gallery[0].url
        : '';
      break;

    case 'thumbnail_count':
      // @todo: Fetch this from the correct key.
      mediaCollection = input.media_gallery;
      value = mediaCollection.length;
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
          selectOption.attr({selected: 'selected', disabled: 'disabled'}).text(rcsTranslatedText(`Select ${option.attribute_code}`));
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
      // @todo: Use the correct image key.
      value = ((input.media_gallery.length > 0)
          && (typeof input.media_gallery[0].url !== 'undefined'
            || input.media_gallery[0].url
            || input.media_gallery[0].url !== '')
        )
        ? input.media_gallery[0].url
        : drupalSettings.alshayaRcs.default_meta_image
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
      value = `${drupalSettings.rcsPhSettings.productPathPrefix}${input.url_key}.html`;
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
      data.legal_notice = {
        enabled: drupalSettings.alshayaRcs.legal_notice_enabled,
        label: drupalSettings.alshayaRcs.legal_notice_label,
        summary: drupalSettings.alshayaRcs.legal_notice_summary,
      };

      // Render handlebars plugin.
      value = handlebarsRenderer.render(`field.product.${filter}`, data);
      break;

    case 'short_description':
      // Prepare the object data for rendering.
      data = (input.short_description) ? input.short_description : input.description;
      data.read_more = false;

      // Apply ellipsis.
      let tmp = applyEllipsis(data.html);
      data.value = tmp.value;
      data.read_more = tmp.read_more;

      // Render handlebars plugin.
      value = handlebarsRenderer.render(`field.product.${filter}`, data);
      break;

    case 'promotions':
      const promotions = input.promotions || {};
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
          link: Drupal.url(promotion.url),
          hreflang: drupalSettings.path.currentLanguage,
          label: promotion.label,
        });
      });
      data.promotions = promotionsList;

      // Render handlebars plugin.
      value = handlebarsRenderer.render(`product.${filter}`, data);
      break;

    default:
      console.log(`Unknown JS filter ${filter}.`)
  }

  return value;
};
