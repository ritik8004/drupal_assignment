/**
 * @file
 * Initialize Alshaya Sprinklr chatbot.
 */

(function (drupalSettings) {
  window.sprChatSettings = window.sprChatSettings || {};
  window.sprChatSettings = {
    'appId': drupalSettings.alshaya_sprinklr.appId,
    'skin':'MODERN',
  };
})(drupalSettings);
