/**
 * Check whether the Javascript code is executed on a browser or on a Node.js server.
 * @return {bool} Whether or not the execution context is browser (in opposition to middleware).
 */
globalThis.rcsPhIsBrowserContext = () => {
  if (typeof global !== 'undefined' && global.rcs_ph_context === 'middleware') {
    return false;
  }

  return true;
}

/**
 * Invoke all required JS to ensure events are properly bound after replacement.
 */
globalThis.rcsPhApplyDrupalJs = (context) => {
  if (!(rcsPhIsBrowserContext())) {
    return;
  }

  Drupal.attachBehaviors(context, drupalSettings);

  if (typeof ARIAmodal !== 'undefined') {
    ARIAmodal.init();
  }
}

globalThis.rcsWindowLocation = () => {
  return rcsPhIsBrowserContext()
    ? window.location
    : global.location;
}

/**
 * Utility function to redirect to page.
 *
 * @param {string} url
 *   The url to redirect to.
 */
 globalThis.rcsRedirectToPage = (url) => {
  const location = rcsWindowLocation();
  location.href = url;
}

// Override Drupal.t() from Drupal Core.
if (typeof Drupal.tOriginal === 'undefined') {
  Drupal.tOriginal = Drupal.t;
  Drupal.t = function (str, args, options) {
    if (rcsPhIsBrowserContext()) {
      return Drupal.tOriginal(str, args, options);
    }

    // Add special markup to ensure it is processed in the browser properly.
    args = args || {};
    options = options || {};
    return '<span class="rcs-drupal-t" data-str="' + str + '" data-args="' + escape(JSON.stringify(args)) + '" data-options="' + escape(JSON.stringify(options)) + '"></span>';
  };
}

globalThis.rcsHtmlDecode = (input) => {
  // For the settings that are read from markup we need to do some special
  // conversion to be able to read them as JSON.
  return input.replace(/\&amp\;/g, '\&')
    .replace(/\&gt\;/g, '\>')
    .replace(/\&lt\;/g, '\<')
    .replace(/\&quot\;/g, '\"')
    .replace(/\&\#39\;/g, '\"');
}

globalThis.rcsReplaceAll = (markup, search, replacement) => {
  // We can't do a simple replace() as it would only replace the
  // first occurrence in the template. We are using a regex so it
  // it replaces all the occurrences. The placeholder replacement
  // must be escaped so all the regex specific characters we use in
  // the placeholders are not conflicting with the regex.
  return markup.replace(
    new RegExp(search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'g'),
    replacement
  );
}

/**
 * Stores a text value for later reuse.
 * Local storage is used for Browser.
 * Global variable is used for Node.js server.
 * @param {string} key The key for the stored data.
 * @param {string} value The value for the stored data.
 */
globalThis.rcsPhStorageSet = (key, value) => {
  if (rcsPhIsBrowserContext()) {
    localStorage.setItem(key, JSON.stringify(value));
  } else {
    if (global.rcs_ph_storage === undefined) {
      global.rcs_ph_storage = [];
    }
    global.rcs_ph_storage[key] = value;
  }
};

/**
 * Deletes a text value for later reuse.
 * Local storage is used for Browser.
 * Global variable is used for Node.js server.
 * @param {string} key The key for the stored data.
 */
globalThis.rcsPhStorageDel = (key) => {
  if (rcsPhIsBrowserContext()) {
    localStorage.removeItem(key);
  } else {
    if (global.rcs_ph_storage === undefined) {
      global.rcs_ph_storage = [];
    }
    global.rcs_ph_storage[key] = null;
  }
};

/**
 * Reads a text value previously stored.
 * Local storage is used for Browser.
 * Global variable is used for Node.js server.
 * @param {string} key The key for the stored data.
 * @return {string|null} The value of the stored data.
 */
globalThis.rcsPhStorageGet = (key) => {
  let value = null;
  if (rcsPhIsBrowserContext()) {
    const ssValue = JSON.parse(localStorage.getItem(key));
    if (ssValue !== null) {
      value = ssValue;
    }
  } else if (
    global.rcs_ph_storage !== undefined &&
    key in global.rcs_ph_storage
  ) {
    value = global.rcs_ph_storage[key];
  }
  return value;
};

/**
 * Identify replacements for entity placeholders.
 * @return {Array} An array of [field, value] where field is the text to be
 * replaced, value is the text to replace with.
 */
globalThis.rcsPhReplaceEntityPh = (sourceHtml, entityType, entity, langcode) => {
  let replacements = [];

  const entityPhRegex = new RegExp('\\#rcs.(' + entityType + '\\.[^#]+)\\#', 'g');
  const entityPhMatches = sourceHtml.match(entityPhRegex);

  if (entityPhMatches && entityPhMatches.length !== 0) {
    let processed = [];
    entityPhMatches.forEach(function eachEntityPh(fieldPh) {
      // Avoid processing twice the same placeholder.
      if (processed.includes(fieldPh)) {
        return;
      }
      processed.push(fieldPh);

      const entityPhDetailsRegex = new RegExp('^\\#rcs.' + entityType + '\\.([^"\\|]+)\\|?([^"]+)?\\#$');
      const [, entityFieldVar, filter] = fieldPh.match(entityPhDetailsRegex);

      // Identify associated value.
      let entityFieldValue = entity;

      // For cases like add to cart we need the whole product object.
      // We use the keyword _self for that.
      if (entityFieldVar !== '_self') {
        entityFieldVar.split('.').forEach(function eachAttribute(attr) {
          const attrRegex = /^([^\[]+)(\[([^\]]+)\])?$/;
          const attrMatches = attr.match(attrRegex);

          if (attrMatches && attrMatches[1] !== undefined && entityFieldValue[attrMatches[1]] !== undefined) {
            entityFieldValue = entityFieldValue[attrMatches[1]];
            if (attrMatches[3] !== undefined && entityFieldValue[attrMatches[3]] !== undefined) {
              entityFieldValue = entityFieldValue[attrMatches[3]];
            }
          }
        });

        if (entityFieldValue !== null && entityFieldValue[langcode] !== undefined) {
          entityFieldValue = entityFieldValue[langcode];
        }
      }

      if (filter !== undefined) {
        entityFieldValue = rcsPhRenderingEngine.computePhFilters(entityFieldValue, filter);

        if (entityFieldValue !== null && entityFieldValue[langcode] !== undefined) {
          entityFieldValue = entityFieldValue[langcode];
        }
      }

      // We initiate the entityFieldValue with the full entity. However, the
      // expected return is an array of values for replacements. These values
      // can only be "scalar" value, not a rich structure (object, array, ...).
      // If for any reason the placeholder does not match an existing attribute
      // in the entity object, we enforce the replacement to be an empty string.
      if (typeof entityFieldValue == "object") {
        entityFieldValue = "";
      }

      replacements.push([fieldPh, entityFieldValue]);
    });
  }

  return replacements;
};

/**
 * Returns the setting if available or null.
 */
globalThis.rcsPhGetSetting = (setting) => {
  return drupalSettings.rcsPhSettings[setting] || null;
}

/**
 * Gets the page type.
 */
globalThis.rcsPhGetPageType = () => typeof drupalSettings.rcsPage !== 'undefined'
                                      && typeof drupalSettings.rcsPage.type !== 'undefined'
                                        ? drupalSettings.rcsPage.type
                                        : null;
