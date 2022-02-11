(function ($, Drupal, drupalSettings) {
  /**
   * All custom js for product pretty path.
   */

  Drupal.getSelectedSkuFromPdpPrettyPath = function (productInfo) {
    let selectedSku = '';
    const swatchParam = productInfo.swatch_param.split('-');
    const variants = productInfo.variants;
    for (let i in variants) {
      for (let j in variants[i]['configurableOptions']) {
        const attrId = variants[i]['configurableOptions'][j]['attribute_id'].replace('attr_', '');
        if (attrId === swatchParam[0]) {
          let swatchVal = variants[i]['configurableOptions'][j]['value'];
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

  Drupal.getSelectedProductFromSwatch = function (sku, selected, productKey) {
    const variantInfo = drupalSettings[productKey][sku];
    const swatchParam = variantInfo.swatch_param.split('-');
    let swatchVal = $('article[data-vmode="full"] .form-item-configurable-swatch option[value="' + selected + '"]').text();
    swatchVal = cleanSwatchVal(swatchVal);

    if (swatchParam[1] !== swatchVal) {
      const url = variantInfo.url.split('/-');
      const newQuery = '/-' + swatchParam[0] + '-' + swatchVal;
      const newUrl = url[0] + newQuery + '.html' + location.search;
      drupalSettings[productKey][sku]['swatch_param'] = swatchParam[0] + '-' + swatchVal;
      window.history.replaceState(variantInfo, variantInfo.title, newUrl);
    }
  };

  const cleanSwatchVal = (swatchVal) => {
    const swatch = swatchVal.replace(/ /g, "_");
    return swatch.toLowerCase();
  };

})(jQuery, Drupal, drupalSettings);
