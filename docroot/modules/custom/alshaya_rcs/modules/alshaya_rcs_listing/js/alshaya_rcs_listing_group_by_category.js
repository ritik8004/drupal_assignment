window.commerceBackend = window.commerceBackend || {};

/**
 * Format / Clean the rule context string.
 *
 * @param {string} context
 *
 * @returns
 *  {string} Formatted or cleaned rule context.
 */
let formatCleanRuleContext = function (context) {
  context = context.trim().toLowerCase();
  // Remove special characters.
  context = context.replaceAll(/[^a-zA-Z0-9 ]/g, '');
  // Ensure duplicate spaces are replaced with single space.
  // H & M would have become H  M after preg_replace.
  context = context.replaceAll(/\s\s+/g, ' ');

  // Replace spaces with underscore.
  context = context.replaceAll(/\s+/g, '_');

  return context;
};

/**
 * Format Grouped subcategory graphql response for rendering.
 *
 * @param {object} data
 * @param {boolean} languageCheck
 *
 * @returns
 *  {object} Formatted grouped subcategory response.
 */
let prepareSubCategoryData = function (data, languageCheck = true) {
  let subCategoryData = {};
  if (!Drupal.hasValue(data) || !Drupal.hasValue(data[0].children)) {
    return {};
  }
  data = data[0];
  data.children.forEach(function(singleCategory, index) {
    // Check if data is valid.
    if (!Drupal.hasValue(singleCategory.plp_group_category_title)
      || !Drupal.hasValue(singleCategory.plp_group_category_img)
      || !Drupal.hasValue(singleCategory.plp_group_category_desc)
      || !Drupal.hasValue(data.filter_value[index])
    ) {
      return;
    }
    // Build renderable response to be used in React PlpApp.
    subCategoryData[singleCategory.id] = {
      tid: singleCategory.id,
      title: singleCategory.plp_group_category_title,
      weight: index + 1,
      image: {
        url: singleCategory.plp_group_category_img,
        alt: singleCategory.plp_group_category_title,
      },
      description: singleCategory.plp_group_category_desc,
    };
    if (languageCheck && window.drupalSettings.path.currentLanguage !== 'en') {
      // For AR language, we build category hierarchy data in EN only.
      // It will be done in recursive call prepareSubCategoryData() below.
      // So no further processing needed for AR data, hence return.
      return;
    }
    let hierarchy = data.filter_value[index];
    let context_list = [];
    let contexts = [];
    hierarchy.split(' > ').forEach(function(item) {
      context_list.push(formatCleanRuleContext(item));
      contexts.push(context_list.join('__'));
    });
    // Merge rule contexts and web rule contexts.
    let ruleContexts = contexts.reverse();
    var webRuleContexts = ruleContexts.map(function (ruleContext) {
      return "web__".concat(ruleContext);
    });
    // Category hierarchy data.
    subCategoryData[singleCategory.id].category = {
      category_field: 'field_category_name.' + data.filter_field,
      level: data.level,
      hierarchy: hierarchy,
      ruleContext: ruleContexts.concat(webRuleContexts),
    };
  });
  // For Arabic, category hierarchy data is needed in EN only.
  // So do an extra graphQl call for EN data, when current language is AR.
  if (languageCheck && Object.keys(subCategoryData).length > 0 && window.drupalSettings.path.currentLanguage !== 'en') {
    // EN graphQl call subcategories data.
    let dataEn = globalThis.rcsPhCommerceBackend.getDataSynchronous('grouped_subcategories', {langcode: 'en'});
    // Recursive call to same function for preparing EN data.
    let filteredDataEn = prepareSubCategoryData(dataEn, false);
    for (let index in subCategoryData) {
      if (Object.keys(filteredDataEn).includes(index)) {
        // Merge EN data inside AR subcategory data.
        subCategoryData[index].category = filteredDataEn[index].category;
      } else {
        // Delete AR data if corresponding EN mapping not found.
        delete subCategoryData[index];
      }
    }
  }
  return subCategoryData;
};


/**
 * Call Graphql for Grouped Subcategories response.
 *
 * @returns {object}
 *   Filtered graphql response for rendering.
 */
window.commerceBackend.getSubcategoryData = function getSubcategoryData() {
  // Make graphql call only if category has grouped subcategories.
  if (window.has_grouped_subcategories) {
    // Call Graphql API.
    let data = globalThis.rcsPhCommerceBackend.getDataSynchronous('grouped_subcategories');
    return prepareSubCategoryData(data);
  }
  return {};
};
