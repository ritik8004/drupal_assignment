/**
 * @file
 * Initialize Sprinklr chatbot.
 */

(function (drupalSettings) {
  window.sprChatSettings = window.sprChatSettings || {};
  let sprChatSettings = {
    'appId': drupalSettings.sprinklr.appId,
    'user': {},
  };

  // Allow other modules to alter sprinklr chat settings.
  document.dispatchEvent(new CustomEvent('sprChatSettingsAlter', {
      bubbles: true,
      detail: sprChatSettings,
    })
  );
  window.sprChatSettings = sprChatSettings;
})(drupalSettings);
