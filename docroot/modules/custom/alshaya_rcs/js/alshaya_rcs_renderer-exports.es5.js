// @codingStandardsIgnoreFile
// This is because the linter is throwing errors where we use backticks here.
// Once we enable webapack for the custom modules directory, we should look into
// removing the above ignore line.
exports.render = function render(
  settings,
  placeholder,
  params,
  inputs,
  entity,
  langcode,
  innerHtml
) {
  let html = '';
  // Process according to the placeholder element.
  switch (placeholder) {
    case "navigation_menu":
      // Process rcs navigation renderer, if available.
      if (typeof globalThis.renderRcsNavigationMenu !== 'undefined') {
        html = globalThis.renderRcsNavigationMenu.render(
          settings,
          inputs,
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

    case 'data-sku':
      value = input.sku;
      break;

    case 'data-sku-type':
      value = input.type_id;
      break;

    case 'vat_text':
      if (drupalSettings.vat_text === '' || drupalSettings.vat_text === null) {
        $('.vat-text').remove();
      }
      value = drupalSettings.vat_text;
      break;

    default:
      console.log(`Unknown JS filter ${filter}.`)
  }

  return value;
};
