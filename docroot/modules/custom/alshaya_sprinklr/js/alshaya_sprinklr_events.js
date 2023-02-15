/**
 * @file
 * Contains Alshaya Sprinklr chatbot functionality.
 */

(function (drupalSettings) {
  // Drupal Settings 'updateConversationContext' variable is
  // used to determine if user is visiting the page right
  // after the login using 'update_conversation_context' cookie
  // for normal login and 'sprinklr_social_login' cookie for
  // social login check 'AlshayaSprinklrEventSubscriber'.
  // Once this variable is added in drupalSettings the cookies are
  // deleted check 'alshaya_sprinklr_page_attachments_alter'.
  if (typeof drupalSettings.sprinklr.updateConversationContext !== 'undefined'
    && drupalSettings.sprinklr.updateConversationContext === true) {
    // Trigger chatbot 'updateConversationContext' &
    // 'openExistingConversation' event to auto open the chatbot
    // when user logs in. Add a delay of 4s to fire this event
    // because the chatbot is not loaded immediately and we don't
    // have any event to check that as of now.
    // Also, triggering this event does not return proper
    // response so we can't determine if it was successful
    // or not. Hence, we are using setTimeout with 4s delay.
    // @todo need to check with sprinklr team to figure out
    // a better approach to handle these events in future.
    setTimeout(() => {
      // Trigger updateConversationContext SDK to let
      // sprinklr chatbot know that the anonymous user
      // has now logged in.
      sprChat('updateConversationContext', {
        context: typeof drupalSettings.sprinklr.clientContext !== 'undefined'
          ? drupalSettings.sprinklr.clientContext
          : {},
      });
      // Open the sprinklr chatbot.
      sprChat('openExistingConversation');
    }, 4000);
  }
})(drupalSettings)
