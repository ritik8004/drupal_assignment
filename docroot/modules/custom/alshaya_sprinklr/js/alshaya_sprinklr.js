/**
 * @file
 * Contains Alshaya Sprinklr chatbot functionality.
 */

(function (drupalSettings) {
  document.addEventListener('sprChatSettingsAlter', (e) => {
    if (typeof drupalSettings.alshayaSprinklr === 'undefined') {
      return;
    }
    var sprChatData = e.detail;
    // Set skin value.
    if (typeof drupalSettings.alshayaSprinklr.skin !== 'undefined') {
      sprChatData.skin = drupalSettings.alshayaSprinklr.skin;
    }
    // For authenticated users.
    if (typeof drupalSettings.alshayaSprinklr.userDetails !== 'undefined') {
      sprChatData.user = drupalSettings.alshayaSprinklr.userDetails;
    }
    // For anonymous users.
    if (typeof drupalSettings.alshayaSprinklr.userContext !== 'undefined') {
      sprChatData.userContext = drupalSettings.alshayaSprinklr.userContext;
    }
  });
})(drupalSettings);
