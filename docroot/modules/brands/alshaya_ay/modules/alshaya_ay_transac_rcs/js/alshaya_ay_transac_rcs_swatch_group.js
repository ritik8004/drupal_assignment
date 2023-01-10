/**
 * Listens to the 'alshayaRcsAlterSwatch' event and the color option list.
 */
 (function main(Drupal, RcsEventManager) {
  // Alter color option list with color grouping values.
  RcsEventManager.addListener('alshayaRcsAlterSwatch', function alshayaRcsAlterSwatch (e) {
    const colorGroupAttribute = 'color_way';
    e.detail.displayColorGroup = true;
    if (Drupal.hasValue(e.detail.variant.product[colorGroupAttribute])) {
      const colorGroupValue = e.detail.variant.product[colorGroupAttribute];
      const colorGroupLabel = window.commerceBackend.getAttributeValueLabel(colorGroupAttribute, colorGroupValue);

      e.detail.colorOptionsList = Object.assign(e.detail.colorOptionsList, {
        'color_group_label': colorGroupLabel,
      });
    } else {
      e.detail.colorOptionsList = Object.assign(e.detail.colorOptionsList, {
        'color_group_label': Drupal.t('Core'),
      });
    }
  });

  // Alter swatch data to push color grouping values.
  RcsEventManager.addListener('alshayaRcsAlterProcessConfigurableCombinations', function alshayaRcsAlterProcessConfigurableCombinations (e) {
    // Initialize with default values to avoid undefined error.
    let swatchData = [];
    const colorAttribute = e.detail.colorDetails.sku_configurable_color_attribute;

    e.detail.combinations.configurables[colorAttribute]['isSwatch'] = false;
    e.detail.combinations.configurables[colorAttribute]['isSwatchGroup'] = false;
    // Set swatch data if available.
    if (Drupal.hasValue(e.detail.combinations.configurables[colorAttribute])) {
      e.detail.combinations.configurables[colorAttribute]['isSwatch'] = true;
      e.detail.combinations.configurables[colorAttribute]['isSwatchGroup'] = true;

      e.detail.combinations.configurables[colorAttribute].values.forEach(function(option, key) {
        e.detail.combinations.configurables[colorAttribute].values[key].swatch_type = 'text';
        if (Drupal.hasValue(e.detail.colorDetails.sku_configurable_options_color[option.value_id])
          && e.detail.colorDetails.sku_configurable_options_color[option.value_id].swatch_type === 'RGB') {
            e.detail.combinations.configurables[colorAttribute].values[key].swatch_color = e.detail.colorDetails.sku_configurable_options_color[option.value_id].display_value;
            e.detail.combinations.configurables[colorAttribute].values[key].swatch_type = e.detail.colorDetails.sku_configurable_options_color[option.value_id].swatch_type;
            e.detail.combinations.configurables[colorAttribute].values[key].color_group = e.detail.colorDetails.sku_configurable_options_color[option.value_id].color_group_label;
        }
      });

      swatchData = e.detail.combinations.configurables[colorAttribute].values;
      if (swatchData.length > 0) {
        // Group array based on color group attribute.
        var result = swatchData.filter(function filterColorConfigurables(item) {
          if (!Drupal.hasValue(item.color_group)) {
            return false;
          }
          return true;
        }).reduce(function (r, a) {
          r[a.color_group] = r[a.color_group] || [];
          r[a.color_group].push(a);
          return r;
        }, Object.create(null));
        //Set swatch with color grouping.
        e.detail.combinations.configurables[colorAttribute].values = result;
      }
    }
  });

})(Drupal, RcsEventManager);
