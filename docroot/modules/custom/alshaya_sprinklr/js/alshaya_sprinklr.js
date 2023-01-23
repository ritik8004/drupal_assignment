/**
 * @file
 * Contains Alshaya Sprinklr chatbot functionality.
 */

(function (Drupal, drupalSettings) {
  document.addEventListener('sprChatSettingsAlter', (e) => {
    if (!Drupal.hasValue(drupalSettings.alshayaSprinklr)) {
      return;
    }
    var sprChatData = e.detail;
    // Set skin value.
    if (Drupal.hasValue(drupalSettings.alshayaSprinklr.skin)) {
      sprChatData.skin = drupalSettings.alshayaSprinklr.skin;
    }
    // For authenticated users.
    if (Drupal.hasValue(drupalSettings.alshayaSprinklr.userDetails)) {
      sprChatData.user = drupalSettings.alshayaSprinklr.userDetails;
    }
    // For anonymous users.
    if (Drupal.hasValue(drupalSettings.alshayaSprinklr.userContext)) {
      sprChatData.userContext = drupalSettings.alshayaSprinklr.userContext;
    }
  });
})(Drupal, drupalSettings);
