/**
 * Fetch the enriched data from the storage if present or call API.
 * @return {object} Object of enriched data.
 */
globalThis.rcsGetEnrichedCategories = () => {
  let enrichedData = globalThis.RcsPhStaticStorage.get('enriched_categories');
  if (enrichedData) {
    return enrichedData;
  }

  jQuery.ajax({
    url: Drupal.url('rest/v2/categories'),
    async: false,
    success: function (data) {
      // Store the value in static storage.
      globalThis.RcsPhStaticStorage.set('enriched_categories', data);
      enrichedData = data;
    }
  });

  return enrichedData;
}

// Link between RCS errors and Datadog.
(function main() {
  RcsEventManager.addListener('error', (e) => {
    Drupal.alshayaLogger(e.level, e.message, e.context);
  });
})();

