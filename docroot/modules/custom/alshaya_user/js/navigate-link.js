/**
 * @file
 * Navigate to previous link on my account.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formatInput = {
    attach: function (context, settings) {
      var $targetLink = $('.my-account-nav', context)
        .find('li > a.active')
        .parent()
        .prev()
        .find('a')
        .prop('href');

      $('#back-link', context).prop('href', $targetLink);
    }
  };
})(jQuery, Drupal);
