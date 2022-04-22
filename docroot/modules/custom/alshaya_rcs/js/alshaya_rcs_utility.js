/**
 * Fetch the enriched data from the storage if present or call API.
 * @return {object} Object of enriched data.
 */
globalThis.rcsGetEnrichedCategories = (callBackFunc = null) => {
  let enrichedData = globalThis.RcsPhStaticStorage.get('enriched_categories');
  if (enrichedData) {
    // Execute the callback to perform further action on data.
    callBackFunc(enrichedData);
    return enrichedData;
  }

  // Set the default value of request started if it doesn't exists.
  if (!Drupal.hasValue(globalThis.RcsPhRestCategory)) {
    globalThis.RcsPhRestCategory = {
      requestStarted: false,
      callBacks: [],
    }
  }

  if (Drupal.hasValue(globalThis.RcsPhRestCategory) && !globalThis.RcsPhRestCategory.requestStarted) {
    // Store the async request status in a global variable.
    globalThis.RcsPhRestCategory.requestStarted = true;
    jQuery.ajax({
      url: Drupal.url('rest/v2/categories'),
      success: function (data) {
        // Execute all the register callbacks.
        if (globalThis.RcsPhRestCategory.callBacks.length > 0) {
          globalThis.RcsPhRestCategory.callBacks.forEach(callBack => {
            callBack(data);
          });
        }
      }
    });
    // Register the callback.
    globalThis.RcsPhRestCategory.callBacks.push(callBackFunc);
  }
  else {
    // Register the callback.
    globalThis.RcsPhRestCategory.callBacks.push(callBackFunc);
  }

  return globalThis.RcsPhStaticStorage.get('enriched_categories');
}

/**
 * Returns the data stored in the static storage for enriched categories.
 * @returns {object} Object of enriched data.
 */
globalThis.rcsSetEnrichedCategoriesInStaticStorage = (data = null) => {
  globalThis.RcsPhStaticStorage.set('enriched_categories', data);
}

// Link between RCS errors and Datadog.
(function main() {
  RcsEventManager.addListener('error', (e) => {
    Drupal.alshayaLogger(e.level, e.message, e.context);
  });
})();

