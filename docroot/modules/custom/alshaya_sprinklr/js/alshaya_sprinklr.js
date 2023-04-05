/**
 * @file
 * Contains Alshaya Sprinklr chatbot functionality.
 */

(function ($, drupalSettings) {
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
  // So to get Sprinklr iframe Id setTimeout is used here.
  setTimeout(() => {
    const sprChatId = $('#spr-live-chat-frame').length;
    const winWidth = $(window).width();
    if(sprChatId > 0 && winWidth < 768) {
      const backToTop = $('#backtotop')
      backToTop.addClass('sprinklr-enabled');
    }
  }, 2000);

})(jQuery, drupalSettings);
