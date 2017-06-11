/**
 * @file
 * Address Book.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.address = {
    attach: function (context, settings) {

      function toggleOverlay(button, className) {
        $(button).click(function () {
          $('body').removeClass(className);
        });
      }

      $('.address--delete a').click(function () {
        $('body').addClass('modal-overlay');

        $(document).ajaxComplete(function () {
          toggleOverlay('.ui-dialog-titlebar-close', 'modal-overlay');
          toggleOverlay('.ui-dialog-buttonpane .dialog-cancel', 'modal-overlay');
        });
      });

      // If address book form.
      if ($('.address-book-address').length) {
        addressBookRemoveBraces();
      }

      // Remove braces around from country code.
      function addressBookRemoveBraces() {
        var country_code_html = $('.address-book-address div.country-select div.prefix').html();
        var country_code_string = country_code_html.replace('(', '').replace(')', '');
        $('.address-book-address div.country-select div.prefix').html(country_code_string);
      }

    }
  };

})(jQuery, Drupal);
