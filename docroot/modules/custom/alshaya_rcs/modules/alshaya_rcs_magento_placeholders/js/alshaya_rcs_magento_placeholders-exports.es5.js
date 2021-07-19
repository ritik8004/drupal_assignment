// @codingStandardsIgnoreFile
// This is because the linter is throwing errors where we use backticks here.
// Once we enable webapack for the custom modules directory, we should look into
// removing the above ignore line.

/**
 * Check if product is available for home delivery.
 *
 * @see alshaya_acm_product_available_home_delivery().
 */
function isProductAvailableForHomeDelivery() {
  return isProductBuyable();
}

/**
 * Check if product is available for click and collect.
 *
 * @see alshaya_acm_product_available_click_collect().
 */
function isProductAvailableForClickAndCollect() {
  // @todo: Implement the same way as
  // alshaya_acm_product_available_click_collect(). Currently some attributes
  // are not available so leaving this as a minimal stub.
  return drupalSettings.alshaya_click_collect.status;
}

/**
 * Check if the product is buyable.
 */
function isProductBuyable() {
  // @todo: Check for the buyable field of the product.
  return true;
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
      const deliveryOptionsWrapper = jQuery('.rcs-templates--delivery_option').clone();
      const cncEnabled = isProductAvailableForClickAndCollect();
      const isDeliveryOptionsAvailable = isProductAvailableForHomeDelivery() || cncEnabled;

      if (isDeliveryOptionsAvailable) {
        const homeDelivery = jQuery('.rcs-templates--delivery_option-home-delivery').clone().children();
        jQuery('.field__content', deliveryOptionsWrapper).append(homeDelivery);

        if (isProductBuyable()) {
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
            // @todo: Add method to clean the SKU value as twig's clean_class.
            // @see: \Drupal\Component\Utility\Html::getClass().
            'data-sku-clean': entity.sku,
          });

          const subTitle = cncEnabled
            ? drupalSettings.alshaya_click_collect.subtitle.enabled
            : drupalSettings.alshaya_click_collect.subtitle.disabled;
          $('.subtitle', clickAndCollect).html(subTitle);

          // Add click and collect to the delivery options field.
          jQuery('.field__content', deliveryOptionsWrapper).append(clickAndCollect);
        }
      }

      html += deliveryOptionsWrapper.html();
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for render.`);
      break;
  }

  return html;
};

exports.computePhFilters = function (input, filter) {
  let value = input;

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

    case 'quantity':
      // @todo Check for how to fetch the max sale quantity.
      const quantity = parseInt(drupalSettings.alshaya_spc.cart_config.max_cart_qty, 10);
      const quantityDroprown = jQuery('.edit-quantity');
      // Remove the quantity filter.
      quantityDroprown.html('');

      for (let i = 1; i <= quantity; i++) {
        if (i === 1) {
          quantityDroprown.append('<option value="' + i + '" selected="selected">' + i + '</option>');
          continue;
        }
        quantityDroprown.append('<option value="' + i + '">' + i + '</option>');
      }
      value = quantityDroprown.html();
      break;

    case 'sku':
      // @todo: Might need to make the value markup safe.
      value = input.sku;
      break;

    case 'sku-type':
      value = input.type_id;
      break;

    case 'vat_text':
      if (drupalSettings.vat_text === '' || drupalSettings.vat_text === null) {
        $('.vat-text').remove();
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

      if (mediaCollection.length > drupalSettings.alshaya_rcs.pdp_gallery_pager_limit) {
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

      // This wrapper will be removed after processing.
      const tempDivWrapper = jQuery('<div>');

      if (typeof input.configurable_options !== 'undefined' && input.configurable_options.length > 0) {
        input.configurable_options.forEach((option) => {
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
          if (drupalSettings.alshaya_rcs.pdp_swatch_attributes.includes(option.attribute_code)) {
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
            selectOption.attr({value: value.value_index}).text(value.label);
            configurableOptionsList.append(selectOption);
          });

          if (typeof drupalSettings.alshaya_rcs.size_guide !== 'undefined'
          && drupalSettings.alshaya_rcs.size_guide.attributes.includes(option.attribute_code)) {
            // Remove '\' added for escaping "".
            const sizeGuideLink = drupalSettings.alshaya_rcs.size_guide.link.replace('\\', '');
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
          if (drupalSettings.add_to_bag.hidden_form_attributes.includes(option.attribute_code)) {
            optionsListWrapper.addClass('hidden');
          }
        });
      }

      // Remove the temporary wrapper.
      value = tempDivWrapper.html();
      break;

    default:
      console.log(`Unknown JS filter ${filter}.`)
  }

  return value;
};
