/**
 * @file
 * Contains Alshaya Sprinklr chatbot functionality.
 */

(function (drupalSettings) {
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

  // For re-positioning Back to top button when sprinklr is enable.
  // SetTimeout is used because the Sprinklr iframe is loading at last.
  // So to get Sprinklr iframe ID setTimeout is used here.
  setTimeout(function () {
    var sprChatId = document.getElementById('spr-live-chat-frame');
    var winWidth = window.innerWidth;
    var backToTop = document.getElementById('backtotop');
    if (sprChatId !== null && backToTop !== null) {
      backToTop.classList.add('sprinklr-enabled');
    }
  }, 2000);

})(drupalSettings);
