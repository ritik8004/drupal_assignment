/**
 * @file
 * Contains Alshaya Sprinklr chatbot functionality.
 */

(function (drupalSettings) {
  // Auto open the sprinklr chatbot when user logs in
  // this is done only once when user logs in via the
  // link provided by chatbot.
  if (typeof drupalSettings.sprinklr.updateConversationContext !== 'undefined'
    && drupalSettings.sprinklr.updateConversationContext === true) {
    // Trigger chatbot 'openExistingConversation' event
    // to auto open the chatbot when user logs in.
    // Add a delay of 3s to fire this event because
    // the chatbot is not loaded immediately and we don't
    // have any event to check that as of now.
    // Also, triggering this event does not return proper
    // response so we can't determine if it was successful
    // or not. Hence, we are using setTimeout with 3s delay.
    setTimeout(() => {
      // Open the sprinklr chatbot.
      sprChat('openExistingConversation');
    }, 3000);
  }
})(drupalSettings)
