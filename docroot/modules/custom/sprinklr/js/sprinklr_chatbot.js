/**
 * @file
 * Initialize Sprinklr chatbot.
 */

(function (drupalSettings) {
  window.sprChatSettings = window.sprChatSettings || {};
  window.sprChatSettings = {
    'appId': drupalSettings.sprinklr.appId,
    'user': {},
  };
})(drupalSettings);
