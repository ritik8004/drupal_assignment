(function ($, Drupal, drupalSettings) {
  'use strict';

  /**
   * Custom js around color split for add to cart form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for add to cart form.
   */
  Drupal.behaviors.alshayaColorSplitGroupConfigurable = {
    attach: function (context, settings) {
      $('.sku-base-form').once('alshaya-color-split').on('variant-selected', function (event, variant, code) {
        var node = $(this).parents('article.entity--type-node:first');
        var sku = $(this).attr('data-sku');
        var productKey = (node.attr('data-vmode') == 'matchback') ? 'matchback' : 'productInfo';
        var productInfo = window.commerceBackend.getProductData(sku, productKey);
        if (productInfo === null) {
          return;
        }
        var variantInfo = productInfo.variants[variant];

        // We can have mix of color split and normal products.
        // Avoid processing further if we have a product which is normal but
        // color split module is enabled.
        if (typeof variantInfo.url === 'undefined') {
          return;
        }

        // Updating the parent sku for selected variant.
        // @see alshaya_acm_product_form_sku_base_form_alter().
        $(this).find('.selected-parent-sku').val(variantInfo.parent_sku);

        // Avoid processing again and again for variants of same color.
        if ($(node).find('.content--item-code .field__value').html() === variantInfo.parent_sku) {
          return;
        }

        var productChanged = false;
        if ($(node).attr('data-vmode') === 'full') {
          if (window.location.pathname !== variantInfo.url[$('html').attr('lang')]) {
            var url = variantInfo.url[$('html').attr('lang')] + location.search;
            url = Drupal.removeURLParameter(url, 'selected');
            window.history.replaceState(variantInfo, variantInfo.title, url);
            productChanged = true;
          }

          $('.language-switcher-language-url .language-link').each(function () {
            $(this).attr('href', variantInfo.url[$(this).attr('hreflang')]);
          });

          // Update dynamic promotions if product is changed.
          if (typeof variantInfo.promotions !== 'undefined' && productChanged) {
            $('.promotions-full-view-mode', node).html(variantInfo.promotions);

            // Reinitialize dynamic promotions if product is changed.
            if (Drupal.alshayaPromotions !== undefined) {
              Drupal.alshayaPromotions.initializeDynamicPromotions(context);
            }
          }

          if (typeof variantInfo.free_gift_promotions !== 'undefined') {
            $('.free-gift-promotions-full-view-mode', node).html(variantInfo.free_gift_promotions);
          }
        }

        $(node).find('.content--item-code .field__value').html(variantInfo.parent_sku);
      });
    }
  };

  /**
   * Get the array of configurable attribute codes for the product.
   *
   * @param {object} product
   *   The product object.
   *
   * @returns {array}
   *   The array of configurable attributes.
   */
  function getProductConfigurableAttributes(product) {
    return product.variants[0].attributes.map(function (attribute) {
      return attribute.code;
    });
  }

  // Add the styled products.
  document.addEventListener('alterProductEntity', function getProductsInStyle(e) {
    // Return if result is empty.
    if (typeof e.detail.result === 'undefined'
      || typeof e.detail.result.style_code === 'undefined'
      || e.detail.result.style_code === null) {
      return;
    }

    // The original object will also be modified in this process.
    const mainProduct = e.detail.result;

    // Get the products with the same style.
    var styleProducts = globalThis.rcsPhCommerceBackend.getDataAsync('products-in-style', { styleCode: mainProduct.style_code });

    // If there are no products with the same style, then no further processing
    // required.
    if (!styleProducts.length) {
      return;
    }

    // This will hold the configugrable options for the main product keyed by
    // the attribute code and then the value index of the options.
    // Eg: {size: {11: {value_index: 10, store_label: "XS"}}}
    // Doing so, will reduce the amount of processing required.
    var mainProductConfigurableOptionsObject = {};
    mainProduct.configurable_options.forEach(function (option) {
      mainProductConfigurableOptionsObject[option.attribute_code] = {};
      // Copy the options of the main product to this object.
      option.values.forEach(function (value) {
        mainProductConfigurableOptionsObject[option.attribute_code][value.value_index] = value;
      });
    });

    const mainProductAttributes = getProductConfigurableAttributes(mainProduct);
    // Alter the configurable variants list of the main product.
    // We will re-populate the variants.
    mainProduct.variants = [];
    // This will store the color values of the styled product.
    const colorAttributeValues = [];

    styleProducts.forEach(function (styleProduct) {
      // Check if product is in stock.

      // Check if attributes of the product is the same as the main product.
      const styleProductAttributes = getProductConfigurableAttributes(styleProduct);

      // Check if the attributes are the same of the main product and the style
      // products.
      var isAttributesSame = mainProductAttributes.length === styleProductAttributes.length;
      mainProductAttributes.forEach(function (mainProductAttribute) {
        if (!styleProductAttributes.includes(mainProductAttribute)) {
          isAttributesSame = false;
          // Break.
          return false;
        }
      });

      if (!isAttributesSame) {
        return;
      }

      // Stores values of processed colors, so that they are not re-processed.
      const processedColors = [];
      styleProduct.variants.forEach(function (variant) {
        // These values will be used later on.
        variant.product.parent_sku = styleProduct.sku;
        variant.product.color_attribute = drupalSettings.alshayaColorSplit.colorAttribute;

        if (!processedColors.includes(variant.product.color)) {
          processedColors.push(variant.product.color);
          // Get the labels for the color attribute.
          const allOptionsForColorAttribute = globalThis.rcsPhCommerceBackend.getDataAsync('product-option', { attributeCode: variant.product.color_attribute });
          // Update the array with the color values.
          colorAttributeValues.push({value_index: variant.product.color, store_label: allOptionsForColorAttribute[variant.product.color]});
        }

        mainProduct.variants.push(variant);
      });

      // Get all the configurable options of the style products.
      styleProduct.configurable_options.forEach(function (styleProductOption) {
        // Add the values of the variant to the option slist.
        styleProductOption.values.forEach(function (value) {
          mainProductConfigurableOptionsObject[styleProductOption.attribute_code][value.value_index] = value;
        });
      });
    });

    // Now alter the configurable options for the main product.
    // Copy the resultant data for the attribute values from
    // mainProductConfigurableOptionsObject to mainProduct.configurable_options.
    Object.entries(mainProduct.configurable_options).forEach(function ([key, mainProductOption]) {
      mainProduct.configurable_options[key].values = [];
      Object.keys(mainProductConfigurableOptionsObject[mainProductOption.attribute_code]).forEach(function (value_index) {
        mainProduct.configurable_options[key].values.push(mainProductConfigurableOptionsObject[mainProductOption.attribute_code][value_index]);
      });
    });

    // Push color to the configurable options of the main product.
    mainProduct.configurable_options.push({
      attribute_uid: btoa(drupalSettings.psudo_attribute),
      label: drupalSettings.alshayaColorSplit.colorLabel,
      position: -1,
      attribute_code: drupalSettings.alshayaColorSplit.colorAttribute,
      values: colorAttributeValues,
    });

    // Sort the configurable options according to position.
    mainProduct.configurable_options = mainProduct.configurable_options.sort(function (optionA, optionB) {
      return (optionA.position > optionB.position) - (optionA.position < optionB.position);
    });

    RcsPhStaticStorage.set('product_' + mainProduct.sku, mainProduct);
  });

})(jQuery, Drupal, drupalSettings);
