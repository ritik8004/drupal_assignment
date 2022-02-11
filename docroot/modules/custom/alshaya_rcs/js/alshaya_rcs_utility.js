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

/**
 * Dispatches custom events
 *
 * @param eventName
 *   The name of the custom event.
 * @param eventDetail
 *   The object containing the custom event details.
 */
dispatchRcsCustomEvent = (eventName, eventDetail) => {
  const event = new CustomEvent(eventName, {
    bubbles: true,
    detail: eventDetail,
  });
  document.dispatchEvent(event);
}

// Link between RCS errors and Datadog.
(function main() {
  RcsEventManager.addListener('error', (e) => {
    Drupal.alshayaLogger(e.level, e.message, e.context);
  });
})();

