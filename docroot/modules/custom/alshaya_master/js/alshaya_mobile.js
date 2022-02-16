/**
 * @file
 * Custom JS to mobile number field to have prefix.
 */

/**
 * @namespace
 */
Drupal.alshayaMobileNumber = Drupal.alshayaMobileNumber || {};

(function ($, Drupal) {

  Drupal.behaviors.alshayaMobileNumber = {
    attach: function (context, settings) {
      $('[mobile-prefix]').once('bind-js').each(function () {
        var element = $(this);
        element.numeric({
          allowMinus   : false,
          allowThouSep : false,
          allowPlus : false,
          allowDecSep: false
        });

        var wrapper = element.closest('.mobile-number-field');
        $(wrapper).find('.prefix').replaceWith('<div class="prefix">' + element.attr('mobile-prefix') + '</div>');
        $('.form-type-select, .form-type-tel', $(wrapper))
          .once()
          .wrapAll('<div class="mobile-input--wrapper"></div>');
      });
    }
  };
})(jQuery, Drupal);
