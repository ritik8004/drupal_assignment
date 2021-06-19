/**
 * Global object containing the RCS APIs.
 */
window.rcs = window.rcs || {};

(function () {
  /**
   * Replaces token in the given text with the given value.
   *
   * @param {string} text
   *   The text containing the tokens.
   * @param {string} token
   *   The token to replace.
   * @param {string} value
   *   The replacement value for the token.
   */
  function replaceTokens(text, token, value) {
    return text.replace(token, value);
  }

  /**
   * Replace the tokens for entities.
   * @param {object} entityType
   *   The entity type.
   * @param {object} entity
   *   The entity for which token replacement is to be done.
   */
  window.rcs.replaceEntityTokens = function (entityType, entity) {
    let replacements = [];
    let body = document.body.innerHTML;

    let placeHolders = body.matchAll('#entity\.' + entityType + '\.(.*?)#');
    placeHolders = [...placeHolders];

    let processedPlaceholders = [];

    placeHolders.forEach(function (placeHolderArray) {
      // 0th element contains the entire placeholder and the 1st element
      // contains the second half of the placeholder, i.e. the grouped part.
      if (typeof processedPlaceholders[placeHolderArray[0]] !== 'undefined') {
        return;
      }

      if (processedPlaceholders.includes(placeHolderArray[0])) {
        processedPlaceholders.push(placeHolderArray[0]);
      }

      // Get the value and store it.
      replacements.push([placeHolderArray[0], entity[placeHolderArray[1]]]);
    });

    replacements.forEach(function (replacement) {
      body = replaceTokens(body, replacement[0], replacement[1]);
    });

    document.body.innerHTML = body;
    // Drupal.attachBehaviors(document);
  }

})();
