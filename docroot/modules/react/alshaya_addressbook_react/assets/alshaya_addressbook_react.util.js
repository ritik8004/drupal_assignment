/**
 * @file
 * JavaScript behaviors of alshaya_addressbook_react.
 */

(function ($, Drupal) {

  // Helper method to display global message.
  Drupal.alshayaAddressBookReactShowGlobalMessage = function (message, type) {
    // Remove any existing message wrapper first.
    Drupal.alshayaAddressBookReactRemoveGlobalMessage();

    var messageWrapper = $(`<div class="messages__wrapper layout-container" />`);
    var messageDiv = $(`<div class="messages messages--${type}"></div>`).html(message);
    messageWrapper.append(messageDiv);
    $('.region__hero-content').prepend(messageWrapper);
    window.scrollTo(0, 0);
  };

  // Helper function to remove the global message.
  Drupal.alshayaAddressBookReactRemoveGlobalMessage = function () {
    // Check if we already have a message wrapper.
    var messageWrapper = '';
    if ($(".messages__wrapper.layout-container").length) {
      messageWrapper = $(".messages__wrapper.layout-container");
      // Empty out the existing message if any.
      messageWrapper.remove();
    }
  }

})(jQuery, Drupal);
