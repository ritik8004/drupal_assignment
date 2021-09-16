/**
 * Fetch the enriched data from the storage if present or call API.
 * @return {object} Object of enriched data.
 */
rcsGetEnrichedCategories = () => {
  let enrichedData = RcsPhStaticStorage.get('enriched_categories');
  if (enrichedData) {
    return enrichedData;
  }
  else {
    jQuery.ajax({
      url: Drupal.url('rest/v2/categories'),
      async: false,
      success: function (data) {
        // Store the value in static storage.
        RcsPhStaticStorage.set('enriched_categories', data);
        enrichedData = data;
      }
    });
  }
  return enrichedData;
}
