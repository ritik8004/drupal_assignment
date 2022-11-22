/**
 * @file
 * Event Listener to alter dynamicYield.
 */

(function (Drupal) {
  document.addEventListener('alterInitialDynamicYield', (e) => {
    // Alter the DY recommendationContext.
    if (e.detail.type === 'category') {
      e.detail.data.recommendationContext = e.detail.data.recommendationContext || {};
      e.detail.data.recommendationContext['type'] = 'CATEGORY';

      var category = e.detail.page_entity;

      // Get the list of all the ancestors.
      // @see ProductCategoryDyPageTypeEventSubscriber
      var data = [];
      if (Drupal.hasValue(category.breadcrumbs)) {
        category.breadcrumbs.forEach(item => {
          data.push(item.category_name);
        });
      }
      // Push the current category item.
      if (category) {
        data.push(category.name);
      }
      e.detail.data.recommendationContext['data'] = data;
    }
  });
})(Drupal);
