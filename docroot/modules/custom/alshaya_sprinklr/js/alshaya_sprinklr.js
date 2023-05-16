/**
 * @file
 * Contains Alshaya Sprinklr chatbot functionality.
 */

(function (Drupal, drupalSettings, once) {
  document.addEventListener('sprChatSettingsAlter', (e) => {
    if (typeof drupalSettings.sprinklr === 'undefined') {
      return;
    }
    var sprChatData = e.detail;
    // Set skin value.
    if (typeof drupalSettings.sprinklr.skin !== 'undefined') {
      sprChatData.skin = drupalSettings.sprinklr.skin;
    }
    // Set client context.
    if (typeof drupalSettings.sprinklr.clientContext !== 'undefined') {
      sprChatData.clientContext = drupalSettings.sprinklr.clientContext;
    }
    // For authenticated users.
    if (typeof drupalSettings.sprinklr.userDetails !== 'undefined') {
      sprChatData.user = drupalSettings.sprinklr.userDetails;
    }
    // For anonymous users.
    if (typeof drupalSettings.sprinklr.userContext !== 'undefined') {
      sprChatData.userContext = drupalSettings.sprinklr.userContext;
    }
  });

  Drupal.behaviors.AlshayaSprinklr = {
    attach: function (context) {
      // Re-position Back to top button when sprinklr is enabled.
      var [backToTop] = once("sprinklr-back-to-top", "#backtotop", context);
      if (backToTop) {
        backToTop.classList.add("sprinklr-enabled");
      }
    },
  };
})(Drupal, drupalSettings, once);
