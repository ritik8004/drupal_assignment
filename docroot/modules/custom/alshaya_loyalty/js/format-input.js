/**
 * @file
 * Format Input.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formatInput = {
    attach: function (context, settings) {
      function formatPrivilegeCard(elem) {
        elem.each(function (e) {
          var value = $(this).val();
          if (value.length > 0) {
            $(this).val(value.match(/\d{4}(?=\d{1,4})|\d+/g).join('-'));
          }
        })

      }

      function preventDefaultValueDeletion(elem, defaultValue) {
        setTimeout(function () {
          if (elem.val().indexOf(defaultValue) !== 0) {
            elem.val(defaultValue);
          }
        }, 1);
      }

      var privilegeCard = $('.c-input__privilege-card');
      var defaultValue = settings.alshaya_loyalty.card_validate.init_value.match(/\d{4}(?=\d{1,4})|\d+/g).join('-');
      formatPrivilegeCard(privilegeCard);

      privilegeCard.each(function (ele) {
        $(this).on('input', function (e) {
          preventDefaultValueDeletion($(this), defaultValue);
          formatPrivilegeCard($(this));
        });
      });
    }
  };
})(jQuery, Drupal);
