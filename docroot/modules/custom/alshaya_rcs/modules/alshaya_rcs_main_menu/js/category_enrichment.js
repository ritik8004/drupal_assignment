/**
 * Get the enriched data from the storage.
 *
 * @return {Array}
 *   Enriched data.
 */
globalThis.rcsGetEnrichedCategories = () => {

  var enrichedCategories = globalThis.RcsPhStaticStorage.get('enriched_categories');
  if (Drupal.hasValue(enrichedCategories)) {
    return enrichedCategories;
  }
  if (drupalSettings.rcs.navigationMenuCacheTime !== 0) {
    return globalThis.RcsPhLocalStorage.get('enriched_categories') || [];
  }
  return [];
}

// Load the enrichment along with categories from Commerce Backend.
(function main(RcsEventManager) {
  RcsEventManager.addListener('invokingApi', function invokingApi (e) {
    var rcsType = e.request.rcsType || '';
    if (rcsType === 'navigation_menu') {
      e.promises.push(jQuery.ajax({
        url: Drupal.url('rest/v2/categories'),
        success: function success (data) {
          globalThis.RcsPhStaticStorage.set('enriched_categories', data);
          if (drupalSettings.rcs.navigationMenuCacheTime !== 0) {
            globalThis.RcsPhLocalStorage.set(
              'enriched_categories',
              data,
              drupalSettings.rcs.navigationMenuCacheTime
            );
          }
        }
      }));
    }
  });
})(RcsEventManager);
