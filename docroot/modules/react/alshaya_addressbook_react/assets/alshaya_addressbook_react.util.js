/**
 * @file
 * JavaScript behaviors of alshaya_addressbook_react.
 */

(function ($, Drupal) {

  // Helper method to display global message.
  Drupal.alshayaAddressBookReactShowGlobalMessage = function (message, type) {
    var messageWrapper = $(`<div class="messages__wrapper layout-container />`);
    var messageDiv = $(`<div class="messages messages--${type}"></div>`).html(message);
    messageWrapper.append(messageDiv);
    $('.region__hero-content').prepend(messageWrapper);
    window.scrollTo(0, 0);
  };

})(jQuery, Drupal);
