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
    if (typeof drupalSettings.sprinklr.updateConversationContext !== 'undefined'
      && drupalSettings.sprinklr.updateConversationContext === true) {
      triggerConversationContextUpdate();
    }
  });

  // Function to handle conversation context update and
  // auto open chatbot for the first time.
  function triggerConversationContextUpdate() {
    // Trigger updateConversationContext SDK to let
    // sprinklr chatbot know that the anonymous user
    // has now logged in.
    sprChat('updateConversationContext', {
      context: typeof drupalSettings.sprinklr.clientContext !== 'undefined'
        ? drupalSettings.sprinklr.clientContext
        : {},
    });
    // Trigger chatbot open SDK to auto-open the chatbot
    // with when user logs in.
    sprChat('openExistingConversation');
  }
})(drupalSettings);
