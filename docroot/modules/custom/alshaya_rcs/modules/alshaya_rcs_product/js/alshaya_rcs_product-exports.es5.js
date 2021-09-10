const Handlebars = require("handlebars");

/**
 * @todo
 *
 * - create module alshaya_rcs_handlebars to have this code
 * - implement a hook to allow modules to register twig templates
 * - after we call the hoook we populate the contents
 * - create a js file with handlebars helpers
 * - create a js file with twig converter
 * - add tests
 */

/**
 * Implements Drupal.t().
 */
Handlebars.registerHelper('t', function(str){
  return Drupal.t(str);
});

// @codingStandardsIgnoreFile

// This is because the linter is throwing errors where we use backticks here.
// Once we enable webapack for the custom modules directory, we should look into
// removing the above ignore line.

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
 * Converts twig templates to handlebars.
 *
 * @param source
 */
function convertToHandlebars(source) {
  let replacements = [
    // Convert translated strings.
    { "{{.*?('.*?')(|t).*?}}": "{{t $1}}" },
    // Make Twig comments become Handlebars strings.
    { "{#": "{{'" },
    { "#}": "'}}" },
    // Convert if statements.
    { "{% if (.*?) %}": "{{#if $1 }}" },
    { "{% endif %}": "{{/if}}" },
  ];

  replacements.forEach(function(item) {
    let search = Object.entries(item)[0][0];
    let replace = Object.entries(item)[0][1];
    source = source.replace(new RegExp(search, 'gm'), replace);
  });

  return source;
}

/**
 * Returns the value from object using nested keys i.e. "field.field_name"
 *
 * @param path string
 *   The path for the value inside the object separated by .
 * @param obj
 *   The object.
 * @param separator
 *   The separator, defaults to .
 * @return {*}
 *   The data.
 */
function resolvePath(path, obj=self, separator='.') {
  var properties = Array.isArray(path) ? path : path.split(separator)
  return properties.reduce((prev, curr) => prev && prev[curr], obj)
}

/**
 * Render Handlebars templates.
 *
 * @param {object} path
 *   The path in the object. i.e "block.block_name"
 *
 * @param {object} data
 *   The data.
 *
 * @returns {object}
 *   Returns the object containing the value and ellipsis information.
 */
function handlebarsRender(template, data) {
  // Get the source template.
  let source = resolvePath(template, rcsTwigTemplates);

  // Convert twig template to handlebar template.
  source = convertToHandlebars(source);

  // Compile source.
  let render = Handlebars.compile(source);

  // Return rendered template using data provided.
  return render(data);
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
function createShortDescription(value) {
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
  let url = '';
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

    case "navigation_menu":
      // Process rcs navigation renderer, if available.
      if (typeof globalThis.renderRcsNavigationMenu !== 'undefined') {
        html += globalThis.renderRcsNavigationMenu.render(
          settings,
          inputs,
          innerHtml
        );
      }
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

    default:
      console.log(`Placeholder ${placeholder} not supported for render.`);
      break;
  }

  return html;
};

exports.computePhFilters = function (input, filter) {
  let value = '';
  let data = {};

  switch(filter) {
    case 'price':
      const priceVal = globalThis.rcsCommerceBackend.getFormattedAmount(input.price.regularPrice.amount.value);
      const finalPriceVal = globalThis.rcsCommerceBackend.getFormattedAmount(input.price.maximalPrice.amount.value);
      const discountVal = globalThis.rcsCommerceBackend.calculateDiscount(priceVal, finalPriceVal);

      const price = jQuery('.rcs-templates--price').clone();
      jQuery('.price-amount', price).html(priceVal);

      const priceBlock = jQuery('.rcs-templates--price_block').clone();

      if (finalPriceVal !== priceVal) {
        const finalPrice = jQuery('.rcs-templates--price').clone();
        jQuery('.price-amount', finalPrice).html(finalPriceVal);

        jQuery('.has--special--price', priceBlock).html(price.html());
        jQuery('.special--price', priceBlock).html(finalPrice.html());

        let discount = jQuery('.price--discount').html();
        discount = discount.replace('@discount', discountVal);
        jQuery('.price--discount', priceBlock).html(discount);
      }
      else {
        // Replace the entire price block html with this one.
        priceBlock.html(price);
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
      value = input.media_gallery[1].url;
      break;

    case 'thumbnail_count':
      // @todo: Fetch this from the correct key.
      mediaCollection = input.media_gallery;
      value = mediaCollection.length;
      break;

    case 'product_thumbnails':
      // Get the template for the thumbnails.
      const thumbnailTemplate = jQuery('.rcs-templates--product_thumbnails').clone();

      // @todo: Fetch this dynamically from the correct key.
      mediaCollection = input.media_gallery;
      // If no media, return;
      if (!mediaCollection.length) {
        value = '';
        return;
      }

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

      value = thumbnails.prop('outerHTML');

      break;

    case 'product_full_screen_gallery':
      const gallery = jQuery('<ul/>');
      mediaCollection = input.media_gallery;

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
        gallery.append(galleryElement);
      });

      value = gallery.html();
      break;

    case 'product_mobile_gallery':
      // Get the template for the thumbnails.
      const mobileGalleryTemplate = jQuery('.rcs-templates--product_gallery_mobile').clone();

      // @todo: Fetch this dynamically from the correct key.
      mediaCollection = input.media_gallery;
      // If no media, return;
      if (!mediaCollection.length) {
        value = '';
        return;
      }

      // This is the list that will hold the thumbnails.
      const mobileGallery = jQuery('#product-image-gallery-mobile');
      // Remove the placeholder from the markup.
      mobileGallery.html('');

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

      value = mobileGallery.html();
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

      // This wrapper will be removed after processing.
      const tempDivWrapper = jQuery('<div>');
      let configurableOptions = input.configurable_options;

      if (typeof configurableOptions !== 'undefined' && configurableOptions.length > 0) {
        const sizeGuide = jQuery('.rcs-templates--size-guide');
        let sizeGuideAttributes = sizeGuide.attr('data-attributes');
        sizeGuideAttributes = sizeGuideAttributes ? sizeGuideAttributes.split(',') : sizeGuideAttributes;
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
            // class: 'form-item-configurable-swatch form-select required valid visually-hidden',
            class: 'form-select required valid',
            name: `configurables[${option.attribute_code}]`,
            'aria-require': true,
            'aria-invalid': false,
          });

          // Check if the attribute is a swatch attribute.
          if (drupalSettings.alshayaRcs.pdpSwatchAttributes.includes(option.attribute_code)) {
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

          // Add the option values.
          option.values.forEach((value) => {
            selectOption = jQuery('<option></option>');
            selectOption.attr({value: value.value_index}).text(value.store_label);
            configurableOptionsList.append(selectOption);
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

      // Replace the placeholder attributes in the sku base form template.
      const attributes = rcsPhGetSetting('placeholderAttributes');
      const pageType = rcsPhGetPageType();

      rcsPhReplaceEntityPh(skuBaseForm.html(), pageType, input, drupalSettings.path.currentLanguage)
        .forEach(function eachReplacement(r) {
          const fieldPh = r[0];
          const entityFieldValue = r[1];
          for (const attribute of attributes) {
            jQuery(`[${ attribute } *= '${ fieldPh }']`, skuBaseForm)
              .each(function eachEntityPhAttributeReplace() {
                jQuery(this).attr(attribute, jQuery(this).attr(attribute).replace(fieldPh, entityFieldValue));
              });
          }
        });

      value = skuBaseForm.html();
      break;

    case 'gtm-price':
      // @todo: Use the correct price key.
      value = input.price.maximalPrice.amount.value;
      break;

    case 'final_price':
      // @todo: Use the correct price key.
      value = input.price.maximalPrice.amount.value;
      break;

    case 'first_image':
      // @todo: Use the correct image key.
      value = (typeof input.media_gallery[1].url === 'undefined' || !input.media_gallery[1].url || input.media_gallery[1].url === '')
        ? drupalSettings.alshayaRcs.default_meta_image
        : input.media_gallery[1].url;
      break;

    case 'schema_stock':
      if (input.stock_status === 'IN_STOCK') {
        value = 'http://schema.org/InStock';
      }
      else {
        value = 'http://schema.org/OutOfStock';
      }
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

    case 'title':
      value = input.name;
      if (typeof rcsGetProductTitle === 'function') {
        value = rcsGetProductTitle(input);
      }
      break;

    case 'description':
      // Prepare the object data for rendering.
      data = {
        label: '',
        value: input.description.html,
      }

      // Brands can define rcsGetProductDescription() to customize how description is generated.
      if (typeof rcsGetProductDescription === 'function') {
        data = rcsGetProductDescription(input);
      }

      // Render twig plugin.
      value = handlebarsRender(`field.${filter}`, data);
      break;

    case 'short_description':
      // Prepare the object data for rendering.
      data = {
        label: '',
        value: input.description.html,
        read_more: false,
      };

      // Brands can define rcsGetProductShortDescription() to customize how short description is generated.
      if (typeof rcsGetProductShortDescription === 'function') {
        data = rcsGetProductShortDescription(input);
      }

      // Compute ellipsis.
      let tmp = createShortDescription(data.value);
      data.value = tmp.value;
      data.read_more = tmp.read_more;

      // Render twig plugin.
      value = handlebarsRender(`field.${filter}`, data);
      break;

    case 'content.legal_notice':
      // @todo add this field
      break;

    default:
      console.log(`Unknown JS filter ${filter}.`)
  }

  return value;
};
