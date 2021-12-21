(function ($, Drupal, drupalSettings) {
  'use strict';
 /**
   * All custom js for product detail page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for product pdp pretty path.
   */
  Drupal.behaviors.alshayaAcmProductPdpPath = {
    attach: function (context, settings) {
      // Update main product on color change.
      $('article[data-vmode="full"] form:first .form-item-configurable-swatch').once('product-swatch-change').on('change', function () {
        var selected = $(this).val();
        var viewMode = $(this).parents('article.entity--type-node').attr('data-vmode');
        var sku = $(this).parents('form').attr('data-sku');
        // Use swatch value to update query param from pretty path.
        getSelectedProductFromSwatch(viewMode, sku, selected);
        // Trigger matchback color change on main product color change.
        Drupal.getSelectedForMatchback(selected);
      });
    }
  };

  Drupal.getSelectedProductFromQueryParam = function (viewMode, productInfo) {
    var variants = productInfo['variants'];
    var swatchParam = productInfo['swatch_param'];
    // Use swatch from query parameter if pdp pretty path is enabled.
    if (swatchParam !== undefined) {
      return getSelectedSkuFromPdpPrettyPath(swatchParam, variants);
    }
    // Use selected from query parameter only for main product.
    Drupal.getSelectedSkuFromQueryParameter(viewMode, variants);
  };

  const getSelectedSkuFromPdpPrettyPath = (swatchParam, variants) => {
    var selectedSku = '';
    var swatchParam = swatchParam.split('-');
    for (var i in variants) {
      for (var j in variants[i]['configurableOptions']) {
        var attrId = variants[i]['configurableOptions'][j]['attribute_id'].replace('attr_', '');
        if (attrId === swatchParam[0]) {
          var swatchVal = variants[i]['configurableOptions'][j]['value'];
          swatchVal = cleanSwatchVal(swatchVal);
          if (swatchVal === swatchParam[1]) {
            selectedSku = variants[i]['sku'];
            break;
          }
        }
      }
      if (selectedSku !== '') {
        break;
      }
    }

    return selectedSku;
  };

  const getSelectedProductFromSwatch = (viewMode, sku, selected) => {
    var productKey = Drupal.getProductKeyForProductViewMode(viewMode);
    var variantInfo = drupalSettings[productKey][sku];
    if (variantInfo.swatch_param !== undefined) {
      var swatchParam = variantInfo.swatch_param.split('-');
      var swatchVal = $('article[data-vmode="' + viewMode + '"] .form-item-configurable-swatch option[value="' + selected + '"]').text();
      swatchVal = cleanSwatchVal(swatchVal);

      if (swatchParam[1] !== swatchVal) {
        var url = variantInfo.url.split('/-');
        var newQuery = '/-' + swatchParam[0] + '-' + swatchVal;
        var newUrl = url[0] + newQuery + '.html' + location.search;
        drupalSettings[productKey][sku]['swatch_param'] = swatchParam[0] + '-' + swatchVal;
        window.history.replaceState(variantInfo, variantInfo.title, newUrl);
      }
    }
  };

  const cleanSwatchVal = (swatchVal) => {
    var swatchVal = swatchVal.replace(/ /g, "_");
    return swatchVal.toLowerCase();
  };

})(jQuery, Drupal, drupalSettings);
