/**
 * Check whether the Javascript code is executed on a browser or on a Node.js server.
 * @return {bool} Whether or not the execution context is browser (in opposition to middleware).
 */
rcsGetEnrichedCategories = () => {
  let enrichedData = RcsPhStaticStorage.get('enriched_categories');
  if (enrichedData) {
    return enrichedData;
  }
  else {
    $.ajax({
      url: Drupal.url('rest/v2/categories'),
      async: false,
      success: function (data) {
        // Store the value in static storage.
        RcsPhStaticStorage.set('enriched_categories', data);
        return data;
      }
    });
  }
}
