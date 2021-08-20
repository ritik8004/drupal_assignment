// Attach all behaviors again as the user has focused on this tab now.
window.onfocus = function () {
  Drupal.attachBehaviors(document, drupalSettings);
};
