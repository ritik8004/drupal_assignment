/**
 * Get the enriched data from the storage.
 *
 * @return {Array}
 *   Enriched data.
 */
globalThis.rcsGetEnrichedCategories = () => {
  return globalThis.RcsPhStaticStorage.get('enriched_categories') || [];
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
        }
      }));
    }
  });
})(RcsEventManager);
