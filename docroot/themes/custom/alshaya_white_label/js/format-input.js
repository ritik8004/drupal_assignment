/**
 * @file
 * Format Input.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.formatInput = {
    attach: function (context, settings) {
      function formatPrivilegeCard(elem) {
        var value = elem.val();
        if (value.length > 0) {
          elem.val(value.match(/\d{4}(?=\d{1,4})|\d+/g).join('-'));
        }
      }

      function preventDefaultValueDeletion(elem, defaultValue) {
        setTimeout(function () {
          if (elem.val().indexOf(defaultValue) !== 0) {
            elem.val(defaultValue);
          }
        }, 1);
      }

      var privilegeCard = $('.c-input__privilege-card');
      formatPrivilegeCard(privilegeCard);
      var defaultValue = privilegeCard.val();

      privilegeCard.on('input', function () {
        preventDefaultValueDeletion($(this), defaultValue);
        formatPrivilegeCard($(this));
      });
    }
  };
})(jQuery, Drupal);
