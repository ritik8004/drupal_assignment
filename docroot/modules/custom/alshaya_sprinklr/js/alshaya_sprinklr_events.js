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
    // Trigger updateConversationContext SDK to let
    // sprinklr chatbot know that the anonymous user
    // has now logged in.
    sprChat('updateConversationContext', {
      context: typeof drupalSettings.sprinklr.clientContext !== 'undefined'
        ? drupalSettings.sprinklr.clientContext
        : {},
    });
    // Auto open the sprinklr chatbot.
    sprChat('openExistingConversation');
  }
})(drupalSettings)
